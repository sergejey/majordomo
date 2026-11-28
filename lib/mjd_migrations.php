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

/**
 * 2021-03-21: cached_values no longer has an EXPIRE column. Old tables keep
 * it NOT NULL without a default, so every REPLACE INTO fails in strict mode.
 */
if (!function_exists('mjd_migration_cached_values_expire_20210321')) {
    function mjd_migration_cached_values_expire_20210321()
    {
        $columns = @SQLSelect("SHOW COLUMNS FROM cached_values LIKE 'EXPIRE'");
        if (!is_array($columns) || count($columns) === 0) {
            return true;
        }
        return @SQLExec("ALTER TABLE cached_values DROP COLUMN EXPIRE") !== false;
    }
}

/** 2024-05-08: the default ping host changed from tut.by to www.google.com. */
if (!function_exists('mjd_migration_pinghosts_google_20240508')) {
    function mjd_migration_pinghosts_google_20240508()
    {
        $row = @SQLSelectOne("SELECT ID FROM pinghosts WHERE ID=1 AND HOSTNAME='tut.by'");
        if (empty($row['ID'])) {
            return true;
        }
        return @SQLExec("UPDATE pinghosts SET HOSTNAME='www.google.com' WHERE ID=1") !== false;
    }
}

/** 2025-12-21: cycle timestamps must be cast to int before subtraction. */
if (!function_exists('mjd_migration_checkstate_int_cast_20251221')) {
    function mjd_migration_checkstate_int_cast_20251221()
    {
        $rows = @SQLSelect("SELECT ID, CODE FROM methods WHERE TITLE IN ('checkState','object17_method59','object17_method73') AND CODE LIKE '%time()-$tm>%'");
        if (!is_array($rows)) {
            return false;
        }
        foreach ($rows as $row) {
            $code = str_replace('time()-$tm>', 'time()-(int)$tm>', $row['CODE']);
            if ($code === $row['CODE']) {
                continue;
            }
            if (!@SQLExec("UPDATE methods SET CODE='" . DBSafe($code) . "' WHERE ID=" . (int)$row['ID'])) {
                return false;
            }
        }
        return true;
    }
}

/** 2026-11-22: command State must use ROOTHTML for local icon paths. */
if (!function_exists('mjd_migration_command97_roothtml_20261122')) {
    function mjd_migration_command97_roothtml_20261122()
    {
        $row = @SQLSelectOne("SELECT ID, CODE FROM commands WHERE ID=97");
        if (empty($row['ID'])) {
            return true;
        }
        $code = str_replace('src="/img/icons/', 'src="<#ROOTHTML#>img/icons/', $row['CODE']);
        if ($code === $row['CODE']) {
            return true;
        }
        // DbSafe1 keeps % placeholders used by processTitle intact.
        return @SQLExec("UPDATE commands SET CODE='" . DbSafe1($code) . "' WHERE ID=97") !== false;
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
            'cached_values_expire_20210321' => 'mjd_migration_cached_values_expire_20210321',
            'pinghosts_google_20240508' => 'mjd_migration_pinghosts_google_20240508',
            'checkstate_int_cast_20251221' => 'mjd_migration_checkstate_int_cast_20251221',
            'command97_roothtml_20261122' => 'mjd_migration_command97_roothtml_20261122',
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
