<?php

$settings_structure = array(
    'default' => array(
        'GENERAL_ALICE_NAME' => array(
            'title' => LANG_COMPUTER_NAME,
        ),
        'GENERAL_START_LAYOUT' => array(
            'title' => LANG_HOMEPAGE_LAYOUT,
            'type' => 'select',
            'data' => '=' . LANG_DEFAULT . '|homepages=' . LANG_OPTION_HOMEPAGES . '|menu=' . LANG_MENU . '|apps=' . LANG_APPLICATIONS . '|cp=' . LANG_CONTROL_PANEL
        )
    ),
    'system' => array(
        'SYSTEM_DISABLE_DEBMES' => array(
            'title' => LANG_DISABLE_LOGGING,
            'type' => 'onoff',
            'DEFAULTVALUE' => '0'
        ),
        'SYSTEM_DEBMES_PATH' => array(
            'title' => LANG_DEBMES_LOGS_PATH,
        ),
        'SYSTEM_DB_MAIN_SAVE_PERIOD' => array(
            'title' => LANG_DB_MAIN_SAVE_PERIOD,
            'default' => '15'
        ),
        'SYSTEM_DB_HISTORY_SAVE_PERIOD' => array(
            'title' => LANG_DB_HISTORY_SAVE_PERIOD,
            'default' => '60'
        )
    ),
    'behavior' => array(
        'BEHAVIOR_NOBODYHOME_TIMEOUT' => array(
            'title' => LANG_NOBODYHOME_TIMEOUT,
            'default' => '60',
            'notes' => LANG_SET_0_TO_DISABLE,
        )
    ),
    'hook' => array(
        'HOOK_BEFORE_SAY' => array(
            'title' => LANG_HOOK_BEFORE_SAY,
            'notes' => LANG_HOOK_BEFORE_SAY_NOTE,
        ),
        'HOOK_AFTER_SAY' => array(
            'title' => LANG_HOOK_AFTER_SAY,
            'notes' => LANG_HOOK_AFTER_SAY_NOTE,
        ),
        'HOOK_EVENT_ASK' => array(
            'title' => LANG_HOOK_EVENT_ASK,
            'notes' => LANG_HOOK_EVENT_ASK_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_COMMAND' => array(
            'title' => LANG_HOOK_EVENT_COMMAND,
            'notes' => LANG_HOOK_EVENT_COMMAND_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_SAY' => array(
            'title' => LANG_HOOK_EVENT_SAY,
            'notes' => LANG_HOOK_EVENT_SAY_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_SAYREPLY' => array(
            'title' => LANG_HOOK_EVENT_SAYREPLY,
            'notes' => LANG_HOOK_EVENT_SAYREPLY_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_SAYTO' => array(
            'title' => LANG_HOOK_EVENT_SAYTO,
            'notes' => LANG_HOOK_EVENT_SAYTO_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_SAY_CACHED_READY' => array(
            'title' => LANG_HOOK_EVENT_SAY_CACHED_READY,
            'notes' => LANG_HOOK_EVENT_SAY_CACHED_READY_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_DEVICE_DELETED' => array(
            'title' => LANG_HOOK_EVENT_DEVICE_DELETED,
            'notes' => LANG_HOOK_EVENT_DEVICE_DELETED_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_PANELBLOCKLEFT' => array(
            'title' => LANG_HOOK_EVENT_PANELBLOCKLEFT,
            'notes' => LANG_HOOK_EVENT_PANELBLOCKLEFT_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_PANELBLOCKRIGHT' => array(
            'title' => LANG_HOOK_EVENT_PANELBLOCKRIGHT,
            'notes' => LANG_HOOK_EVENT_PANELBLOCKRIGHT_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_HOURLY' => array(
            'title' => LANG_HOOK_EVENT_HOURLY,
            'notes' => LANG_HOOK_EVENT_HOURLY_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_DAILY' => array(
            'title' => LANG_HOOK_EVENT_DAILY,
            'notes' => LANG_HOOK_EVENT_DAILY_NOTE,
            'type' => 'json',
        ),
        'HOOK_EVENT_MINUTELY' => array(
            'title' => LANG_HOOK_EVENT_MINUTELY,
            'notes' => LANG_HOOK_EVENT_MINUTELY_NOTE,
            'type' => 'json',
        ),
        'HOOK_BARCODE' => array(
            'title' => LANG_HOOK_BARCODE,
            'notes' => LANG_HOOK_BARCODE_NOTE,
        ),
        'HOOK_PLAYMEDIA' => array(
            'title' => LANG_HOOK_PLAYMEDIA,
            'notes' => LANG_HOOK_PLAYMEDIA_NOTE,
        ),
        'HOOK_BEFORE_PLAYSOUND' => array(
            'title' => LANG_HOOK_BEFORE_PLAYSOUND,
            'notes' => LANG_HOOK_BEFORE_PLAYSOUND_NOTE,
        ),
        'HOOK_AFTER_PLAYSOUND' => array(
            'title' => LANG_HOOK_AFTER_PLAYSOUND,
            'notes' => LANG_HOOK_AFTER_PLAYSOUND_NOTE,
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
            'title' => LANG_VERTICAL_NAVIGATION
        ),
        'SCENES_BACKGROUND' => array(
            'title' => LANG_PATH_TO_BACKGROUND,
            'type' => 'path'
        ),
        'SCENES_BACKGROUND_VIDEO' => array(
            'title' => LANG_PATH_TO_VIDEO_BACKGROUND,
            'type' => 'path'
        ),
        'SCENES_CLICKSOUND' => array(
            'title' => LANG_PATH_TO_CLICKSOUND,
            'type' => 'path'
        ),
        'SCENES_BACKGROUND_FIXED' => array(
            'title' => LANG_BACKGROUND_FIXED,
            'type' => 'onoff',
            'default' => '0'
        ),
        'SCENES_BACKGROUND_NOREPEAT' => array(
            'title' => LANG_BACKGROUND_NO_REPEAT,
            'type' => 'onoff',
            'default' => '0'
        )
    ),
    'backup' => array(
        'BACKUP_PATH' => array(
            'title' => LANG_PATH_TO_STORE_BACKUP,
        )
    ),
    'mail' => array(
        'MAIL_TYPE' => array(
            'title' => LANG_PROTOCOL,
            'priority' => '10',
            'type' => 'select',
            'data' => 'smtp=SMTP|sendmail=SendMail',
        ),
        'MAIL_HOST' => array('title' => LANG_SMTP_HOST, 'priority' => 9),
        'MAIL_AUTH' => array('title' => LANG_AUTHORIZATION_REQUIRED, 'type' => 'onoff', 'default' => 0, 'priority' => 8),
        'MAIL_USER' => array('title' => LANG_SMTP_USERNAME, 'priority' => 7),
        'MAIL_PASSWORD' => array('title' => LANG_SMTP_PASSWORD, 'priority' => 6),
        'MAIL_PORT' => array('title' => LANG_SMTP_PORT, 'priority' => 5),
        'MAIL_SECURE' => array('title' => LANG_SMTP_SECURITY, 'type' => 'select', 'data' => '=' . LANG_NONE . '|ssl=SSL|tls=TLS', 'priority' => 4),
    ),
    'remote' => array(
        'REMOTE_HOME_NETWORK' => array(
            'title' => LANG_GENERAL_HOME_NETWORK,
            'notes' => LANG_HOME_NETWORK_NOTE,
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