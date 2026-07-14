<?php

if (isset($params['brightness'])) {
    $params['NEW_VALUE'] = $params['brightness'];
} elseif (isset($params['value'])) {
    $params['NEW_VALUE'] = $params['value'];
} elseif (isset($params['VALUE'])) {
    $params['NEW_VALUE'] = $params['VALUE'];
}

require DIR_MODULES . 'devices/SRGB_brightnessUpdated.php';
