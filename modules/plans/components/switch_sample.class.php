<?php

include_once DIR_MODULES . 'plans/plan_component.class.php';

class switch_sample extends plan_component {

    function __construct($id)
    {
        $this->name=str_replace('.class.php','',basename(__FILE__));
        parent::__construct($id);
    }

    function getProperties()
    {
        $properties = parent::getProperties();
        $properties[] = array(
            'NAME' => 'color',
            'TITLE' => LANG_COLOR,
            'TYPE' => 'rgb',
            'DEFAULT' => '#ff0000'
        );
        $properties[] = array(
            'NAME' => 'bgcolor',
            'TITLE' => LANG_COLOR.' (background)',
            'TYPE' => 'select',
            'DATA' => '#ff0000=Red|#00ff00=Green|#0000ff=Blue',
            'DEFAULT' => '#ff0000'
        );        
        $properties[] = array(
            'NAME' => 'value',
            'TITLE' => LANG_VALUE,
            'TYPE' => 'linked_property'
        );
        /*
        $properties[] = array(
            'NAME' => 'value_min',
            'TITLE' => 'Min',
            'TYPE' => 'int',
            'DEFAULT' => '0'
        );
        $properties[] = array(
            'NAME' => 'value_max',
            'TITLE' => 'Max',
            'TYPE' => 'int',
            'DEFAULT' => '100'
        );
        */
        
        $this->processProperties($properties);
        
        return $properties;
    }
    
    function getSVG($attributes)
    {

        $x=(int)$attributes['x'];
        $y=(int)$attributes['y'];

        $data=$this->getData();

        $current_value=(int)$data['value']['VALUE'];
        if ($data['value']['VALUE']) {
            $data['value_proc']['VALUE']=100;
        } else {
            $data['value_proc']['VALUE']=0;
        }

        $bgcolor=$data['bgcolor']['VALUE'];

        $width=(int)$attributes['width'];
        if (!$width) $width=200;
        $height=(int)$attributes['height'];
        if (!$height) $height=20;

        $svg="<svg width='$width' height='$height' x='$x' y='$y'>";
        $svg.="<rect x='0' y='0' width='100%' height='$height' style=\"fill:{$bgcolor};stroke-width:3;stroke:rgb(0,0,0)\"/>";
        $svg.="<rect x='0' y='0' width='%value_proc%%' id='procElem{$this->component_id}' height='$height' style=\"fill:%color%;stroke-width:3;stroke:rgb(0,0,0)\"/>";
        $svg.="</svg>";
        foreach($data as $k=>$v) {
            $svg=str_replace('%'.$k.'%',$v['VALUE'],$svg);
        }
        return $svg;
    }
    
    function getJavascript($attributes)
    {
        $data=$this->getData();
        $prop_name=strtolower($data['value']['LINKED_OBJECT'].'.'.$data['value']['LINKED_PROPERTY']);
        $code = <<<EOD
    function componentUpdated{$this->component_id}(property_name,property_value) {
        if (property_name.toLowerCase()=='$prop_name') {
        var elem=$('#procElem{$this->component_id}');
        if (property_value=='1') {
        elem.attr('widthNum','5');
         elem.animate(
         {'widthNum':100},
            {
            step: function(widthNum){
                 $(this).attr('width', widthNum+'%');
            },
            duration: 200
            }
            );
    
        } else {
         elem.attr('widthNum','100');
         elem.animate(
            {'widthNum':0},
            {
            step: function(widthNum){
                 $(this).attr('width', widthNum+'%');
            },
            duration: 200
            }
            );
        }
        }
    }
EOD;
        return $code;
    }
}