<?php

include_once DIR_MODULES . 'plans/plan_component.class.php';

class iframe extends plan_component
{

    function __construct($id)
    {
        $this->name = str_replace('.class.php', '', basename(__FILE__));
        parent::__construct($id);
    }

    function getProperties()
    {
        $properties = parent::getProperties();
        $properties[] = array(
            'NAME' => 'url',
            'TITLE' => 'URL',
            'TYPE' => 'text',
            'DEFAULT' => '/menu.html'
        );

        $properties[] = array(
            'NAME' => 'scale',
            'TITLE' => LANG_APPEAR_SCALE.' (%)',
            'TYPE' => 'int',
            'DEFAULT' => '100'
        );

        $properties[] = array(
            'NAME' => 'bgcolor',
            'TITLE' => LANG_COLOR.' (background)',
            'TYPE' => 'rgb',
            'DEFAULT' => 'white'
        );

        $this->processProperties($properties);
        return $properties;
    }

    function getSVG($attributes)
    {

        $x = (int)$attributes['x'];
        $y = (int)$attributes['y'];

        $data = $this->getData();

        $bgcolor = $data['bgcolor']['VALUE'];

        $url = $data['url']['VALUE'];
        $scaleProc=(int)$data['scale']['VALUE'];
        if (!$scaleProc) {
            $scaleProc=100;
        }
        $scale = round($scaleProc/100,2);
        $scaleProc=(1/$scale)*100;

        $width = (int)$attributes['width'];
        if (!$width) $width = 200;
        $height = (int)$attributes['height'];
        if (!$height) $height = 200;

        $svg = "<svg width='$width' height='$height' x='$x' y='$y'>";
        $svg .= "<g transform=\"scale($scale)\">";
        $svg .= "<foreignObject x=\"0\" y=\"0\" width=\"$scaleProc%\" height=\"$scaleProc%\">";
        $svg .= "<iframe src=\"$url\" style=\"width: 100%; height: 100%;background-color:$bgcolor;\" frameborder='0'>";
        $svg .= "</iframe>";
        $svg .= "</foreignObject>";
        $svg .= "</g>";
        $svg .= "</svg>";
        return $svg;
    }

    function getJavascript($attributes)
    {
        $code = '';
        return $code;
    }
}