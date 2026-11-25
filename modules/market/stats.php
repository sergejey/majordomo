<?php
/**
 * Local update statistics. No network requests, no personal data.
 * Stored as ThisComputer properties.
 */
if (!function_exists('mksStatsAdd')) {
    function mksStatsAdd($type, $name = '')
    {
        if (!function_exists('gg') || !function_exists('sg')) {
            return;
        }
        $type = preg_replace('/[^a-z]/i', '', (string)$type);
        $name = preg_replace('/[^A-Za-z0-9_]/', '', (string)$name);
        if ($type === '') {
            return;
        }
        $key = 'UpdateStats_' . $type . '_count';
        sg($key, (int)gg($key) + 1);
        sg('UpdateStats_' . $type . '_last', date('Y-m-d H:i:s'));
        sg('UpdateStats_last', date('Y-m-d H:i:s'));
        if ($name !== '') {
            $mkey = 'UpdateStats_' . $type . '_' . $name;
            sg($mkey, (int)gg($mkey) + 1);
        }
    }

    function mksStatsGet()
    {
        if (!function_exists('gg')) {
            return array();
        }
        return array(
            'core_updates' => (int)gg('UpdateStats_core_count'),
            'core_last' => (string)gg('UpdateStats_core_last'),
            'module_updates' => (int)gg('UpdateStats_module_count'),
            'module_skips' => (int)gg('UpdateStats_skip_count'),
            'last' => (string)gg('UpdateStats_last')
        );
    }
}
