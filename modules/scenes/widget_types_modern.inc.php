<?php

$this->widget_types['slideshow'] = array(
    'TITLE' => LANG_WIDGET_SLIDESHOW,
    'DESCRIPTION' => LANG_WIDGET_SLIDESHOW_DESCRIPTION,
    'PROPERTIES' => array(
        'folder' => array('DESCRIPTION' => LANG_WIDGET_SLIDESHOW_FOLDER,
            'DEFAULT_VALUE' => 'cms/scenes/backgrounds', '_CONFIG_TYPE' => 'text'),
        'interval' => array('DESCRIPTION' => LANG_WIDGET_SLIDESHOW_INTERVAL,
            'DEFAULT_VALUE' => '5', '_CONFIG_TYPE' => 'text'),
        'fit' => array('DESCRIPTION' => LANG_WIDGET_SLIDESHOW_FIT,
            'DEFAULT_VALUE' => 'cover', '_CONFIG_TYPE' => 'select',
            '_CONFIG_OPTIONS' => array(
                array('VALUE' => 'cover', 'TITLE' => LANG_WIDGET_SLIDESHOW_FIT_COVER),
                array('VALUE' => 'contain', 'TITLE' => LANG_WIDGET_SLIDESHOW_FIT_CONTAIN)
            ))
    ),
    'RENDER_FUNCTION' => function ($data) {
        $folder = isset($data['folder']) ? str_replace('\\', '/', trim((string)$data['folder'])) : '';
        $folder = ltrim($folder, '/');
        $interval = isset($data['interval']) ? max(1, (int)$data['interval']) : 5;
        $slides = array();
        if ($folder !== '' && !preg_match('/^[A-Za-z]:/', $folder) && strpos($folder, '..') === false && is_dir(ROOT . $folder)) {
            $files = glob(ROOT . $folder . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            if (!is_array($files)) { $files = array(); }
            sort($files);
            foreach ($files as $i => $file) {
                $url = ROOTHTML . $folder . '/' . basename($file);
                $slides[] = '<img class="mj-slide" src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="" style="animation-delay:' . ($i * $interval) . 's">';
            }
        }
        $count = count($slides);
        $fit = isset($data['fit']) && $data['fit'] === 'contain' ? 'contain' : 'cover';
        return array('slides_html' => implode('', $slides), 'duration' => $interval * ($count ? $count : 1), 'fit' => $fit);
    },
    'RESIZABLE' => 1,
    'DEFAULT_WIDTH' => 320,
    'DEFAULT_HEIGHT' => 200,
    'TEMPLATE' => 'file:slideshow_block.html'
);

$this->widget_types['progress'] = array(
    'TITLE' => LANG_WIDGET_PROGRESS,
    'DESCRIPTION' => LANG_WIDGET_PROGRESS_DESCRIPTION,
    'PROPERTIES' => array(
        'object' => array('DESCRIPTION' => LANG_WIDGET_PROGRESS_OBJECT,
            '_CONFIG_TYPE' => 'select',
            '_CONFIG_OPTIONS' => function () {
                return SQLSelect("SELECT ID as VALUE, TITLE FROM objects ORDER BY TITLE");
            }),
        'property' => array('DESCRIPTION' => LANG_WIDGET_PROGRESS_PROPERTY,
            'DEFAULT_VALUE' => '', '_CONFIG_TYPE' => 'text'),
        'max_object' => array('DESCRIPTION' => LANG_WIDGET_PROGRESS_MAX_OBJECT,
            '_CONFIG_TYPE' => 'select',
            '_CONFIG_OPTIONS' => function () {
                $options = array(array('VALUE' => '', 'TITLE' => LANG_DEFAULT));
                return array_merge($options, SQLSelect("SELECT ID as VALUE, TITLE FROM objects ORDER BY TITLE"));
            }),
        'max_property' => array('DESCRIPTION' => LANG_WIDGET_PROGRESS_MAX_PROPERTY,
            'DEFAULT_VALUE' => '', '_CONFIG_TYPE' => 'text'),
        'max_constant' => array('DESCRIPTION' => LANG_WIDGET_PROGRESS_MAX_CONSTANT,
            'DEFAULT_VALUE' => '100', '_CONFIG_TYPE' => 'text'),
        'color' => array('DESCRIPTION' => LANG_WIDGET_PROGRESS_COLOR,
            'DEFAULT_VALUE' => '#4a7c0f', '_CONFIG_TYPE' => 'color'),
        'show_label' => array('DESCRIPTION' => LANG_WIDGET_PROGRESS_SHOW_LABEL,
            'DEFAULT_VALUE' => '1', '_CONFIG_TYPE' => 'yesno'),
        'label' => array('DESCRIPTION' => LANG_WIDGET_PROGRESS_LABEL,
            'DEFAULT_VALUE' => '', '_CONFIG_TYPE' => 'text')
    ),
    'RENDER_FUNCTION' => function ($data) {
        $value = 0;
        if (!empty($data['object']) && !empty($data['property'])) {
            $value = (float)getGlobal($data['object'] . '.' . $data['property']);
        }
        $max = 100;
        if (!empty($data['max_object']) && !empty($data['max_property'])) {
            $max = (float)getGlobal($data['max_object'] . '.' . $data['max_property']);
        } elseif (isset($data['max_constant']) && $data['max_constant'] !== '') {
            $max = (float)$data['max_constant'];
        }
        if ($max <= 0) { $max = 100; }
        $percent = (int)max(0, min(100, round($value / $max * 100)));
        $color = isset($data['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $data['color']) ? $data['color'] : '#4a7c0f';
        $label = isset($data['label']) ? htmlspecialchars((string)$data['label'], ENT_QUOTES, 'UTF-8') : '';
        $show_label = !empty($data['show_label']);
        return array(
            'percent' => $percent,
            'color' => $color,
            'value_label' => $show_label ? $label . ' ' . $percent . '%' : ''
        );
    },
    'RESIZABLE' => 1,
    'DEFAULT_WIDTH' => 240,
    'DEFAULT_HEIGHT' => 48,
    'TEMPLATE' => 'file:progress_block.html'
);
