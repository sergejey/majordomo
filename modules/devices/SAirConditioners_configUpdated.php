<?php

// Rebuilds the <option> lists used by the device widget for the fan speed
// and the thermostat mode selectors.
//
// Every mode is translated through a language constant when such a constant
// exists. A driver may report modes that have no translation, and the whole
// property may be empty, so the constant is never requested blindly:
// constant() throws an Error for an undefined constant since PHP 8, and that
// Error is not an Exception, so callMethod() does not catch it and the request
// dies in the middle of the page.

$ac_mode_sets = array();
$ac_mode_sets['fanSpeedModesHTML'] = array('fanSpeedModes', 'LANG_DEVICES_AC_FAN_SPEED_');
$ac_mode_sets['thermostatModesHTML'] = array('thermostatModes', 'LANG_DEVICES_AC_THERMOSTAT_');
foreach ($ac_mode_sets as $ac_target => $ac_set) {
    $ac_modes = (string)$this->getProperty($ac_set[0]);
    $ac_html = '';
    if ($ac_modes != '') {
        $ac_list = explode(',', $ac_modes);
        foreach ($ac_list as $ac_mode) {
            $ac_mode = trim($ac_mode);
            if ($ac_mode == '') {
                continue;
            }
            $ac_constant = $ac_set[1] . strtoupper($ac_mode);
            if (defined($ac_constant)) {
                $ac_title = constant($ac_constant);
            } else {
                $ac_title = $ac_mode;
            }
            $ac_html .= '<option value="' . htmlspecialchars($ac_mode, ENT_QUOTES) . '">' . htmlspecialchars($ac_title, ENT_QUOTES) . '</option>';
        }
    }
    $this->setProperty($ac_target, $ac_html);
}
