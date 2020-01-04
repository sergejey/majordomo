<?php

$plan_id=$rec['ID'];

$component_id=gr('component_id');

if ($component_id) {
    $component = SQLSelectOne("SELECT * FROM plan_components WHERE PLAN_ID=" . (int)$plan_id . " AND ID=" . (int)$component_id);

    if (gr('duplicate') && $component['ID']) {
        $old_id=$component['ID'];
        unset($component['ID']);
        $component['TITLE'].=' (copy)';
        $component['REPLACE_NAME']='';
        $component['ID']=SQLInsert('plan_components',$component);

        $old_data=SQLSelect("SELECT * FROM plan_components_data WHERE COMPONENT_ID=".$old_id);
        foreach($old_data as $data_item) {
            unset($data_item['ID']);
            $data_item['COMPONENT_ID']=$component['ID'];
            SQLInsert('plan_components_data',$data_item);
        }

        $this->redirect("?view_mode=".$this->view_mode."&id=".$plan_id."&tab=".$this->tab."&component_id=".$component['ID']);
    }
    if (gr('delete') && $component['ID']) {
        SQLExec("DELETE FROM plan_components_data WHERE COMPONENT_ID=".$component['ID']);
        SQLExec("DELETE FROM plan_components WHERE ID=".$component['ID']);
        $this->redirect("?view_mode=".$this->view_mode."&id=".$plan_id."&tab=".$this->tab);
    }
    if ($this->mode=='update') {
        $ok=1;
        $component['PLAN_ID']=$plan_id;
        $component['TITLE']=gr('component_title');
        if (!$component['TITLE']) {
            $ok=0;
            $out['ERR_TITLE'];
        }
        $component['COMPONENT_NAME']=gr('component_component_name');
        if (!$component['COMPONENT_NAME']) {
            $ok=0;
            $out['ERR_COMPONENT_NAME']=1;
        }

        $component['REPLACE_NAME']=gr('component_replace_name','trim');

        $do_on_click=gr('do_on_click');
        if ($do_on_click=='run_code') {
            $component['CODE']=gr('code');
        } else {
            $component['CODE']='';
        }
        if ($do_on_click=='run_script') {
            $component['SCRIPT_ID']=gr('script_id');
        } else {
            $component['SCRIPT_ID']=0;
        }
        if ($do_on_click=='open_menu') {
            $component['MENU_ITEM_ID']=gr('menu_item_id');
        } else {
            $component['MENU_ITEM_ID']=0;
        }
        if ($do_on_click=='run_method') {
            $component['ACTION_OBJECT']=gr('action_object');
            $component['ACTION_METHOD']=gr('action_method');
        } else {
            $component['ACTION_OBJECT']='';
            $component['ACTION_METHOD']='';
        }
        if ($do_on_click=='show_homepage') {
            $component['HOMEPAGE_ID']=gr('homepage_id','int');
        } else {
            $component['HOMEPAGE_ID']=0;
        }
        if ($do_on_click=='show_url') {
            $component['EXT_URL']=gr('ext_url');
        } else {
            $component['EXT_URL']='';
        }

        if ($ok) {
            if ($component['ID']) {
                SQLUpdate('plan_components',$component);
            } else {
                $component['ID']=SQLInsert('plan_components',$component);
                $component_id = $component['ID'];
            }
            $out['OK']=1;
            //$this->redirect("?view_mode=".$this->view_mode."&id=".$plan_id."&tab=".$this->tab);
        } else {
            $out['ERR']=1;
        }
    }

    if ($component['COMPONENT_NAME'] && file_exists(DIR_MODULES.$this->name.'/components/'.$component['COMPONENT_NAME'].'.class.php')) {
        include_once (DIR_MODULES.$this->name.'/components/'.$component['COMPONENT_NAME'].'.class.php');
        $object=new $component['COMPONENT_NAME']($component['ID']);
        $properties=$object->getProperties();
        if ($this->mode=='update') {
            SQLExec("DELETE FROM plan_components_data WHERE COMPONENT_ID=".(int)$component['ID']);
            foreach($properties as $property) {
                $property_rec=array('COMPONENT_ID'=>$component['ID']);
                $property_rec['PROPERTY_NAME']=$property['NAME'];
                if ($property['TYPE']=='int') {
                    $property_rec['PROPERTY_VALUE'] = gr('property_'.$property['NAME'], 'int');
                } elseif ($property['TYPE']=='float') {
                    $property_rec['PROPERTY_VALUE'] = gr('property_'.$property['NAME'], 'float');
                } elseif ($property['TYPE']=='linked_property') {
                    $property_rec['LINKED_OBJECT'] = gr('property_'.$property['NAME'] . '_linked_object');
                    $property_rec['LINKED_PROPERTY'] = gr('property_'.$property['NAME'] . '_linked_property');
                } else {
                    $property_rec['PROPERTY_VALUE']=gr('property_'.$property['NAME'],'trim');
                }
                SQLInsert('plan_components_data',$property_rec);
            }
            $properties=$object->getProperties();
        }
        $out['RENDER_SVG']=$object->getSVG(array('x'=>0,'y'=>0));
        $out['PROPERTIES']=$properties;
    }

    if (is_array($component)) {
        foreach ($component as $k => $v) {
            $out['COMPONENT_' . $k] = htmlspecialchars($v);
        }
    }

    $out['COMPONENT_ID']=$component_id;

    $out['HOMEPAGES']=SQLSelect("SELECT ID, TITLE FROM layouts ORDER BY TITLE");
    $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
    $menu_items=SQLSelect("SELECT ID, TITLE, PARENT_ID FROM commands WHERE EXT_ID=0 ORDER BY PARENT_ID, TITLE");
    $titles=array();
    foreach($menu_items as $k=>$v) {
        $titles[$v['ID']]=$v['TITLE'];
    }
    $total=count($menu_items);
    for($i=0;$i<$total;$i++) {
        if ($titles[$menu_items[$i]['PARENT_ID']]) {
            $menu_items[$i]['TITLE']=$titles[$menu_items[$i]['PARENT_ID']].' &gt; '.$menu_items[$i]['TITLE'];
        }
    }
    $out['MENU_ITEMS']=$menu_items;

}

$qry="1";
$components=SQLSelect("SELECT * FROM plan_components WHERE PLAN_ID=".(int)$plan_id." AND $qry ORDER BY TITLE");
$out['COMPONENTS']=$components;
$out['TYPES']=$this->getComponentTypes();
$out['ITEMS']=$this->getImageItems($plan_id);