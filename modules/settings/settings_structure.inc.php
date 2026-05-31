<?php

$settings_structure = array(
    'default' => array(
        'GENERAL_ALICE_NAME' => array(
            'title' => 'Computer\'s name',
        ),
        'GENERAL_START_LAYOUT' => array(
            'title' => 'Homepage Layout',
            'type' => 'select',
            'data' => '=Default|homepages=Home Pages|menu=Menu|apps=Applications|cp=Control Panel'
        )
    ),
    'system' => array(
        'SYSTEM_DISABLE_DEBMES' => array(
            'title' => 'Disable logging (DebMes)',
            'type' => 'onoff',
            'DEFAULTVALUE' => '0'
        ),
        'SYSTEM_DEBMES_PATH' => array(
            'title' => 'Path to DebMes logs',
        ),
        'SYSTEM_DB_MAIN_SAVE_PERIOD' => array(
            'title' => 'Database save period (main data), minutes',
            'default' => '15'
        ),
        'SYSTEM_DB_HISTORY_SAVE_PERIOD' => array(
            'title' => 'Database save period (history data), minutes',
            'default' => '60'
        )
    ),
    'behavior' => array(
        'BEHAVIOR_NOBODYHOME_TIMEOUT' => array(
            'title' => 'NobodyHome mode activation timeout (minutes)',
            'default' => '60',
            'notes' => 'Set 0 to disable',
        )
    ),
    'hook' => array(
        'HOOK_BARCODE' => array(
            'title' => 'Bar-code reading (code)',
        ),
        'HOOK_PLAYMEDIA' => array(
            'title' => 'Playmedia (code)',
        ),
        'HOOK_BEFORE_PLAYSOUND' => array(
            'title' => 'Before PlaySound (code)',
        ),
        'HOOK_AFTER_PLAYSOUND' => array(
            'title' => 'After PlaySound (code)'
        )
    ),
    'codeeditor' => array(
        'CODEEDITOR_TURNONSETTINGS' => array(
            'title' => LANG_CODEEDITOR_TURNONSETTINGS,
            'type' => 'onoff'
        ),
        'CODEEDITOR_SHOWLINE' => array(
            'title' => LANG_CODEEDITOR_SHOWLINE,
            'type' => 'select',
            'data' => '10=10|35=35|45=45|100=100|500=500|1000=1000|99999=' . LANG_CODEEDITOR_BYCODEHEIGHT
        ),
        'CODEEDITOR_MIXLINE' => array(
            'title' => LANG_CODEEDITOR_MIXLINE,
            'type' => 'select',
            'data' => '5=5|10=10|25=25|40=40|1=' . LANG_CODEEDITOR_BYCODEHEIGHT
        ),
        'CODEEDITOR_UPTOLINE' => array(
            'title' => LANG_CODEEDITOR_UPTOLINE,
            'type' => 'onoff'
        ),
        'CODEEDITOR_SHOWERROR' => array(
            'title' => LANG_CODEEDITOR_SHOWERROR,
            'type' => 'onoff'
        ),
        'CODEEDITOR_AUTOCLOSEQUOTES' => array(
            'title' => LANG_CODEEDITOR_AUTOCLOSEQUOTES,
            'type' => 'onoff'
        ),
        'CODEEDITOR_WRAPLINES' => array(
            'title' => LANG_CODEEDITOR_WRAPLINES,
            'type' => 'onoff'
        ),
        'CODEEDITOR_AUTOCOMPLETE' => array(
            'title' => LANG_CODEEDITOR_AUTOCOMPLETE,
            'type' => 'onoff'
        ),
        'CODEEDITOR_AUTOCOMPLETE_TYPE' => array(
            'title' => LANG_CODEEDITOR_AUTOCOMPLETE_TYPE,
            'type' => 'select',
            'data' => 'none=' . LANG_DEFAULT . '|php=' . LANG_CODEEDITOR_AUTOCOMPLETE_TYPE_ONLYPHP . '|phpmjdm=' . LANG_CODEEDITOR_AUTOCOMPLETE_TYPE_PHPMJDM . '|mjdmuser=' . LANG_CODEEDITOR_AUTOCOMPLETE_TYPE_MJDMUSER . '|user=' . LANG_CODEEDITOR_AUTOCOMPLETE_TYPE_USER . '|all=' . LANG_CODEEDITOR_AUTOCOMPLETE_TYPE_PHPMJDMUSER . '',
            'default' => 'codemirror'
        ),
        'CODEEDITOR_THEME' => array(
            'title' => LANG_CODEEDITOR_THEME,
            'type' => 'select',
            'data' => 'codemirror=' . LANG_DEFAULT . '|smoke_theme=SmoKE xD Theme|ambiance=Ambiance|base16-light=base16-light|dracula=Dracula|icecoder=Icecoder|material=Material|moxer=Moxer|neat=Neat',
            'default' => 'codemirror'
        ),
        'CODEEDITOR_AUTOSAVE' => array(
            'title' => LANG_CODEEDITOR_AUTOSAVE,
            'type' => 'select',
            'data' => '0=' . LANG_CODEEDITOR_AUTOSAVE_PARAMS_ONLY_HANDS . '|5=' . LANG_CODEEDITOR_AUTOSAVE_PARAMS_EVERY_5 . '|10=' . LANG_CODEEDITOR_AUTOSAVE_PARAMS_EVERY_10 . '|15=' . LANG_CODEEDITOR_AUTOSAVE_PARAMS_EVERY_15 . '|30=' . LANG_CODEEDITOR_AUTOSAVE_PARAMS_EVERY_30 . '|60=' . LANG_CODEEDITOR_AUTOSAVE_PARAMS_EVERY_60
        )
    ),
    'scenes' => array(
        'SCENES_VERTICAL_NAV' => array(
            'title' => 'Vertical navigation'
        ),
        'SCENES_BACKGROUND' => array(
            'title' => 'Path to background',
            'type' => 'path'
        ),
        'SCENES_BACKGROUND_VIDEO' => array(
            'title' => 'Path to video background',
            'type' => 'path'
        ),
        'SCENES_CLICKSOUND' => array(
            'title' => 'Path to click-sound file',
            'type' => 'path'
        ),
        'SCENES_BACKGROUND_FIXED' => array(
            'title' => 'Backround Fixed',
            'type' => 'onoff',
            'default' => '0'
        ),
        'SCENES_BACKGROUND_NOREPEAT' => array(
            'title' => 'Background No repeat',
            'type' => 'onoff',
            'default' => '0'
        )
    ),
    'backup' => array(
        'BACKUP_PATH' => array(
            'title' => 'Path to store backup',
        )
    ),
    'mail' => array(
        'MAIL_TYPE' => array(
            'title' => 'Protocol',
            'priority' => '10',
            'type' => 'select',
            'data' => 'smtp=SMTP|sendmail=SendMail',
        ),
        'MAIL_HOST' => array('title' => 'SMTP host', 'priority' => 9),
        'MAIL_AUTH' => array('title' => 'Authorization required', 'type' => 'onoff', 'default' => 0, 'priority' => 8),
        'MAIL_USER' => array('title' => 'SMTP username', 'priority' => 7),
        'MAIL_PASSWORD' => array('title' => 'SMTP password', 'priority' => 6),
        'MAIL_PORT' => array('title' => 'SMTP port', 'priority' => 5),
        'MAIL_SECURE' => array('title' => 'SMTP security', 'type' => 'select', 'data' => '=None|ssl=SSL|tls=TLS', 'priority' => 4),
    ),
    'remote' => array(
        'REMOTE_HOME_NETWORK' => array(
            'title' => LANG_GENERAL_HOME_NETWORK,
            'notes' => 'e.g. 192.168.0.*',
            'priority' => 10,
        ),
        'REMOTE_EXT_ACCESS_USERNAME' => array(
            'title' => LANG_USERNAME,
            'priority' => 5,
        ),
        'REMOTE_EXT_ACCESS_PASSWORD' => array(
            'title' => LANG_PASSWORD,
            'priority' => 4,
        )
    )
);