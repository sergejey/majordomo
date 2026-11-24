<?php
/**
 * Idempotent post-update migrations for existing MajorDoMo installations.
 *
 * Every migration has a stable id and is executed at most once. Applied ids
 * are stored in the settings table, so an update from any older version
 * reaches the same final state. The runner is called from load_settings.php.
 */

if (!function_exists('mjd_migration_is_applied')) {
    function mjd_migration_is_applied($id)
    {
        $name = 'MJ_MIGRATION_' . $id;
        $rec = @SQLSelectOne("SELECT VALUE FROM settings WHERE NAME='" . DBSafe($name) . "'");
        return isset($rec['VALUE']) && $rec['VALUE'] !== '';
    }
}

if (!function_exists('mjd_migration_mark_applied')) {
    function mjd_migration_mark_applied($id)
    {
        $name = 'MJ_MIGRATION_' . $id;
        $value = date('Y-m-d H:i:s');
        $rec = @SQLSelectOne("SELECT NAME FROM settings WHERE NAME='" . DBSafe($name) . "'");
        if (isset($rec['NAME'])) {
            @SQLExec("UPDATE settings SET VALUE='" . DBSafe($value) . "' WHERE NAME='" . DBSafe($name) . "'");
        } else {
            @SQLExec("INSERT INTO settings (NAME, VALUE, NOTES, DATA) VALUES ('" . DBSafe($name) . "', '" . DBSafe($value) . "', '', '')");
        }
    }
}

/**
 * The Rooms.updateActivityStatus method used to declare a global rooms_cmp()
 * function in eval(), which caused a fatal error on the second call.
 */
if (!function_exists('mjd_migration_rooms_activity_status')) {
    function mjd_migration_rooms_activity_status()
    {
        $class = @SQLSelectOne("SELECT ID FROM classes WHERE TITLE='Rooms'");
        if (empty($class['ID'])) {
            return true; // no Rooms class, nothing to patch
        }
        $code = "require(DIR_MODULES . 'devices/Rooms_updateActivityStatus.php');";
        $rows = @SQLSelect("SELECT ID, CODE FROM methods WHERE TITLE='updateActivityStatus' AND CLASS_ID=" . (int)$class['ID']);
        if (!is_array($rows)) {
            return false;
        }
        foreach ($rows as $row) {
            if (trim($row['CODE']) === $code) {
                continue;
            }
            if (!@SQLExec("UPDATE methods SET CODE='" . DBSafe($code) . "' WHERE ID=" . (int)$row['ID'])) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('mjd_run_migrations')) {
    function mjd_run_migrations()
    {
        if (!function_exists('SQLSelectOne') || !defined('ROOT')) {
            return;
        }

        // Add new migrations here. Ids must never be reused or renamed.
        $migrations = array(
            'rooms_activity_status_20261124' => 'mjd_migration_rooms_activity_status',
        );

        foreach ($migrations as $id => $callback) {
            if (mjd_migration_is_applied($id)) {
                continue;
            }
            if (!function_exists($callback)) {
                continue;
            }
            $ok = false;
            try {
                $ok = $callback() === true;
            } catch (Exception $e) {
                $ok = false;
                if (function_exists('DebMes')) {
                    DebMes('Migration ' . $id . ' failed: ' . $e->getMessage(), 'migrations');
                }
            } catch (Throwable $e) {
                $ok = false;
                if (function_exists('DebMes')) {
                    DebMes('Migration ' . $id . ' failed: ' . $e->getMessage(), 'migrations');
                }
            }
            if ($ok) {
                mjd_migration_mark_applied($id);
            }
        }
    }
}
