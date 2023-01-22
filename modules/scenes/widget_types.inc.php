<?php

$this->widget_types = array(
    'text' => array(
        'TITLE' => 'Text block',
        'DESCRIPTION' => 'This widget allows you to add text block',
        'PROPERTIES' => array(
            'text_value' => array('DESCRIPTION' => 'Text block', 'DEFAULT_VALUE' => 'Hello, world!', '_CONFIG_TYPE' => 'textarea'),
            'text_size' => array('DESCRIPTION' => 'Text size (pt)',
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
                )
            ),
            'text_align' => array('DESCRIPTION' => 'Text alignment',
                'DEFAULT_VALUE' => 'left',
                '_CONFIG_TYPE' => 'select',
                '_CONFIG_OPTIONS' => array(
                    array('VALUE' => 'left'),
                    array('VALUE' => 'center'),
                    array('VALUE' => 'right')
                )
            ),
            'text_color' => array('DESCRIPTION' => 'Text color', 'DEFAULT_VALUE' => '#ffffff', '_CONFIG_TYPE' => 'color'),
            'text_background' => array('DESCRIPTION' => 'Background color', 'DEFAULT_VALUE' => '#000000', '_CONFIG_TYPE' => 'color'),
            'background_opacity' => array('DESCRIPTION' => 'Background opacity, %',
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
            'text_background_rgba' => array('FUNCTION'=> function($data) {
                list($r, $g, $b) = sscanf($data['text_background'], "#%02x%02x%02x");
                return "$r, $g, $b, ".$data['background_opacity'];
            })
        ),
        'RESIZABLE' => 1,
        'DEFAULT_WIDTH' => 200,
        'DEFAULT_HEIGHT' => 200,
        'TEMPLATE' => 'file:text_block.html'
    ),
    'image'=> array(
        'TITLE' => 'Image block',
        'DESCRIPTION' => 'This widget allows you to add image by URL',
        'PROPERTIES' => array(
            'image_url' => array('DESCRIPTION' => 'Image URL', 'DEFAULT_VALUE' => '', '_CONFIG_TYPE' => 'text'),
            'refresh_interval' => array('DESCRIPTION' => 'Refresh interval, seconds', 'DEFAULT_VALUE' => '0', '_CONFIG_TYPE' => 'text'),
        ),
        'RESIZABLE' => 1,
        'DEFAULT_WIDTH' => 200,
        'DEFAULT_HEIGHT' => 200,
        'TEMPLATE' => 'file:image_block.html'
    )
);

$addons_dir=dirname(__FILE__).'/addons';
if (is_dir($addons_dir)) {
    $addon_files=scandir($addons_dir);
    foreach($addon_files as $file) {
        if (preg_match('/\.php$/',$file)) {
            require($addons_dir.'/'.$file);
        }
    }
}