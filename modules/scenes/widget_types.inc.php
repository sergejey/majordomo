<?php

$this->widget_types = array(
    'text' => array(
        'TITLE' => LANG_WIDGET_TEXT_BLOCK,
        'DESCRIPTION' => LANG_WIDGET_TEXT_BLOCK_DESCRIPTION,
        'PROPERTIES' => array(
            'text_value' => array('DESCRIPTION' => LANG_WIDGET_TEXT_BLOCK_VALUE,
                'DEFAULT_VALUE' => LANG_WIDGET_TEXT_BLOCK_DEFAULT_VALUE,
                '_CONFIG_TYPE' => 'textarea'),
            'text_size' => array('DESCRIPTION' => LANG_WIDGET_TEXT_BLOCK_SIZE,
                'DEFAULT_VALUE' => '10',
                '_CONFIG_TYPE' => 'select',
                '_CONFIG_OPTIONS' => array(
                    array('VALUE' => '8'),
                    array('VALUE' => '10'),
                    array('VALUE' => '12'),
                    array('VALUE' => '14'),
                    array('VALUE' => '16'),
                    array('VALUE' => '18'),
                    array('VALUE' => '22'),
                    array('VALUE' => '26'),
                    array('VALUE' => '32'),
                    array('VALUE' => '44'),
                    array('VALUE' => '52'),
                    array('VALUE' => '65'),
                    array('VALUE' => '72'),
                    array('VALUE' => '86'),
                    array('VALUE' => '95'),
                    array('VALUE' => '112'),
                )
            ),
            'text_align' => array('DESCRIPTION' => LANG_WIDGET_TEXT_ALIGNMENT,
                'DEFAULT_VALUE' => 'left',
                '_CONFIG_TYPE' => 'select',
                '_CONFIG_OPTIONS' => array(
                    array('VALUE' => 'left', 'TITLE' => LANG_WIDGET_TEXT_ALIGNMENT_LEFT),
                    array('VALUE' => 'center', 'TITLE' => LANG_WIDGET_TEXT_ALIGNMENT_CENTER),
                    array('VALUE' => 'right', 'TITLE' => LANG_WIDGET_TEXT_ALIGNMENT_RIGHT)
                )
            ),
            'text_color' => array('DESCRIPTION' => LANG_WIDGET_TEXT_BLOCK_COLOR,
                'DEFAULT_VALUE' => '#ffffff', '_CONFIG_TYPE' => 'color'),
            'text_background' => array('DESCRIPTION' => LANG_WIDGET_TEXT_BLOCK_BACKGROUND_COLOR,
                'DEFAULT_VALUE' => '#000000', '_CONFIG_TYPE' => 'color'),
            'background_opacity' => array('DESCRIPTION' => LANG_WIDGET_TEXT_BLOCK_BACKGROUND_OPACITY,
                'DEFAULT_VALUE' => '0.5',
                '_CONFIG_TYPE' => 'select',
                '_CONFIG_OPTIONS' => array(
                    array('VALUE' => '0', 'TITLE' => '0'),
                    array('VALUE' => '0.1', 'TITLE' => '10'),
                    array('VALUE' => '0.2', 'TITLE' => '20'),
                    array('VALUE' => '0.3', 'TITLE' => '30'),
                    array('VALUE' => '0.4', 'TITLE' => '40'),
                    array('VALUE' => '0.5', 'TITLE' => '50'),
                    array('VALUE' => '0.6', 'TITLE' => '60'),
                    array('VALUE' => '0.7', 'TITLE' => '70'),
                    array('VALUE' => '0.8', 'TITLE' => '80'),
                    array('VALUE' => '0.9', 'TITLE' => '90'),
                    array('VALUE' => '1', 'TITLE' => '100')
                )
            ),
            'text_background_rgba' => array('FUNCTION' => function ($data) {
                list($r, $g, $b) = sscanf($data['text_background'], "#%02x%02x%02x");
                return "$r, $g, $b, " . $data['background_opacity'];
            })
        ),
        'RESIZABLE' => 1,
        'DEFAULT_WIDTH' => 200,
        'DEFAULT_HEIGHT' => 200,
        'TEMPLATE' => 'file:text_block.html'
    ),
    'image' => array(
        'TITLE' => LANG_WIDGET_IMAGE_BLOCK,
        'DESCRIPTION' => LANG_WIDGET_IMAGE_BLOCK_DESCRIPTION,
        'PROPERTIES' => array(
            'image_url' => array('DESCRIPTION' => LANG_WIDGET_IMAGE_URL,
                'DEFAULT_VALUE' => '', '_CONFIG_TYPE' => 'image_url'),
            'refresh_interval' => array('DESCRIPTION' => LANG_WIDGET_IMAGE_REFRESH_INTERVAL,
                'DEFAULT_VALUE' => '0', '_CONFIG_TYPE' => 'text'),
        ),
        'RESIZABLE' => 1,
        'DEFAULT_WIDTH' => 200,
        'DEFAULT_HEIGHT' => 200,
        'TEMPLATE' => 'file:image_block.html'
    ),
    'device_scaled' => array(
        'TITLE' => LANG_DEVICE . ' (scaled)',
        'DESCRIPTION' => LANG_DEVICE,
        'PROPERTIES' => array(
            'device_id' => array(
                'DESCRIPTION' => LANG_DEVICE,
                '_CONFIG_TYPE' => 'select',
                '_CONFIG_OPTIONS' => function () {
                    $options = SQLSelect("SELECT ID as VALUE, TITLE FROM devices ORDER BY TITLE");
                    return $options;
                }),
            'view' => array(
                'DESCRIPTION' => LANG_CLASS_TEMPLATE,
                '_CONFIG_TYPE' => 'select',
                '_CONFIG_OPTIONS' => function () {
                    $options = array(
                        array('VALUE'=>'','TITLE'=>LANG_DEFAULT),
                        array('VALUE'=>'mini','TITLE'=>'Mini')
                    );
                    return $options;
                }
            ),
            'viewbox_width' => array('FUNCTION' => function ($data) {
                if ($data['view']=='mini') {
                    return 60;
                } else {
                    return 260;
                }
            }),
            'widget_background' => array('DESCRIPTION' => LANG_WIDGET_TEXT_BLOCK_BACKGROUND_COLOR,
                'DEFAULT_VALUE' => '#000000', '_CONFIG_TYPE' => 'color'),
            'background_opacity' => array('DESCRIPTION' => LANG_WIDGET_TEXT_BLOCK_BACKGROUND_OPACITY,
                'DEFAULT_VALUE' => '0.5',
                '_CONFIG_TYPE' => 'select',
                '_CONFIG_OPTIONS' => array(
                    array('VALUE' => '0', 'TITLE' => '0'),
                    array('VALUE' => '0.1', 'TITLE' => '10'),
                    array('VALUE' => '0.2', 'TITLE' => '20'),
                    array('VALUE' => '0.3', 'TITLE' => '30'),
                    array('VALUE' => '0.4', 'TITLE' => '40'),
                    array('VALUE' => '0.5', 'TITLE' => '50'),
                    array('VALUE' => '0.6', 'TITLE' => '60'),
                    array('VALUE' => '0.7', 'TITLE' => '70'),
                    array('VALUE' => '0.8', 'TITLE' => '80'),
                    array('VALUE' => '0.9', 'TITLE' => '90'),
                    array('VALUE' => '1', 'TITLE' => '100')
                )
            ),
            'widget_background_rgba' => array('FUNCTION' => function ($data) {
                list($r, $g, $b) = sscanf($data['widget_background'], "#%02x%02x%02x");
                return "$r, $g, $b, " . $data['background_opacity'];
            })
        ),
        'RESIZABLE' => true,
        'DEFAULT_WIDTH' => 260,
        'DEFAULT_HEIGHT' => 60,
        'TEMPLATE' => 'file:device_block.html'
    )
);

$addons_dir = dirname(__FILE__) . '/addons';
if (is_dir($addons_dir)) {
    $addon_files = scandir($addons_dir);
    foreach ($addon_files as $file) {
        if (preg_match('/\.php$/', $file)) {
            require($addons_dir . '/' . $file);
        }
    }
}