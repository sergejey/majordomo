<?php
/*
* @version 0.1 (wizard)
*/

  global $view_mode2;
  $out['VIEW_MODE2']=$view_mode2;


  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='scenes';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

  global $state_id;


  if ($view_mode2=='') {

  if ($this->mode=='update') {
   $ok=1;
  // step: default
  if ($this->tab=='') {
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'BACKGROUND' (varchar)
   global $background;
   $rec['BACKGROUND']=(int)$background;

  //updating 'PRIORITY' (int)
   global $priority;
   $rec['PRIORITY']=(int)$priority;

   global $hidden;
   $rec['HIDDEN']=(int)$hidden;

  // updating elements array
  /*
   global $elements;
   $elements = json_decode($elements, true);
   $elements = ($elements == null) ? array() : $elements;
   */
  }
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
     /*
         foreach ($elements as $value) {
                SQLUpdate('elements', $value);
         }
     */
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }

  }

  if ($view_mode2=='clone_elements') {
   global $element_id;
   $element=SQLSelectOne("SELECT * FROM elements WHERE ID='".(int)$element_id."'");
   $states=SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='".$element['ID']."'");
   unset($element['ID']);
   $element['TITLE']=$element['TITLE'].' (copy)';
   $element['ID']=SQLInsert('elements', $element);

   $total=count($states);
   for($i=0;$i<$total;$i++) {
    unset($states[$i]['ID']);
    $states[$i]['ELEMENT_ID']=$element['ID'];
    SQLInsert('elm_states', $states[$i]);
   }
   $this->redirect("?id=".$rec['ID']."&view_mode=".$this->view_mode."&tab=".$this->tab);
  }


  if ($view_mode2=='delete_elements') {
   global $element_id;
   $element=SQLSelectOne("SELECT * FROM elements WHERE ID='".(int)$element_id."'");
   if ($element['ID']) {
    $this->delete_elements($element['ID']);
    $this->redirect("?id=".$rec['ID']."&view_mode=".$this->view_mode."&tab=".$this->tab);
   }
  }

  if ($view_mode2=='edit_elements') {
   global $element_id;
   $element=SQLSelectOne("SELECT * FROM elements WHERE ID='".(int)$element_id."'");
   $states=SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='".$element['ID']."'");

   if (!$element['SCENE_ID']) {
    $out['ELEMENT_SCENE_ID']=$rec['ID'];
   }

   if ($state_id) {
    $state_rec=SQLSelectOne("SELECT * FROM elm_states WHERE ID='".$state_id."'");
    if (!$rec['ID']) {
     $state_id='';
    }
   } else {
    $state_rec=SQLSelectOne("SELECT * FROM elm_states WHERE ELEMENT_ID='".$element_id."' ORDER BY ID");
    $state_id=$state_rec['ID'];
   }


    global $state_clone;
    if ($state_clone && $state_rec['ID']) {
     $state_rec=SQLSelectOne("SELECT * FROM elm_states WHERE ID='".$state_id."'");
     $state_rec['TITLE'].=' copy';
     unset($state_rec['ID']);
     $state_rec['ID']=SQLInsert('elm_states', $state_rec);
     $this->redirect("?id=".$rec['ID']."&view_mode=".$this->view_mode."&tab=".$this->tab."&view_mode2=".$view_mode2."&element_id=".$element_id."&state_id=".$state_rec['ID']);
    }


   if ($this->mode=='update') {
    $ok=1;
    global $title;
    $element['TITLE']=$title;
    if (!$element['TITLE']) {
     $ok=0;
     $out['ERR_TITLE']=1;
    }

    global $priority;
    $element['PRIORITY']=(int)$priority;


    global $position_type;
    $element['POSITION_TYPE']=(int)$position_type;


    if ($element['POSITION_TYPE']==0) {
     global $linked_element_id;
     if ($linked_element_id==$element['ID']) {
      $linked_element_id=0;
     }
     $element['LINKED_ELEMENT_ID']=(int)$linked_element_id;

     global $top;
     $element['TOP']=(int)$top;

     global $left;
     $element['LEFT']=(int)$left;
    }

    global $type;
    $element['TYPE']=$type;

    global $css_style;
    $element['CSS_STYLE']=$css_style;
    if (!$element['CSS_STYLE']) {
     $element['CSS_STYLE']='default';
    }

    global $container_id;
    if ($element['TYPE']!='container') {
     $element['CONTAINER_ID']=(int)$container_id;
    } else {
     $element['CONTAINER_ID']=0;
    }


    global $scene_id;
    $element['SCENE_ID']=$scene_id;

    global $height;
    $element['HEIGHT']=(int)$height;

    global $width;
    $element['WIDTH']=(int)$width;

    global $background;
    $element['BACKGROUND']=(int)$background;


    global $use_javascript;
    if ($use_javascript) {
     global $javascript;
     $element['JAVASCRIPT']=$javascript;
    } else {
     $element['JAVASCRIPT']='';
    }

    global $use_css;
    if ($use_css) {
     global $css;
     $element['CSS']=$css;
    } else {
     $element['CSS']='';
    }


    global $cross_scene;
    $element['CROSS_SCENE']=(int)$cross_scene;

    //$element['SCENE_ID']=$rec['ID'];

    if ($ok) {
     $out['OK']=1;
     if ($element['ID']) {
      SQLUpdate('elements', $element);
     } else {
      $element['ID']=SQLInsert('elements', $element);
     }
    }

    global $state_title_new;
    global $html_new;
    global $image_new;
    global $script_id_new;
    global $menu_item_id_new;
    global $action_object_new;
    global $action_method_new;
    global $is_dynamic_new;
    global $linked_object_new;
    global $linked_property_new;
    global $condition_new;
    global $condition_value_new;
    global $condition_advanced_new;
    global $switch_scene_new;
    global $state_id;
    global $state_delete;
    global $state_clone;
    global $ext_url_new;
    global $homepage_id_new;
    global $do_on_click_new;

    if ($state_delete && $state_rec['ID']) {

     $state_rec=SQLSelectOne("SELECT * FROM elm_states WHERE ID='".$state_id."'");
     foreach($state_rec as $k=>$v) {
      $out['STATE_'.$k]='';
     }
     SQLExec("DELETE FROM elm_states WHERE ID='".$state_rec['ID']."'");

    } elseif ($state_title_new) {

     $state_rec['ELEMENT_ID']=$element['ID'];
     $state_rec['TITLE']=$state_title_new;
     $state_rec['IMAGE']=$image_new;
     $state_rec['HTML']=$html_new;
     $state_rec['IS_DYNAMIC']=$is_dynamic_new;
     $state_rec['LINKED_OBJECT']=$linked_object_new;
     $state_rec['LINKED_PROPERTY']=$linked_property_new;
     $state_rec['CONDITION']=$condition_new;
     $state_rec['CONDITION_VALUE']=$condition_value_new;
     $state_rec['CONDITION_ADVANCED']=$condition_advanced_new;

     if ($do_on_click_new!='run_script') {
      $script_id_new=0;
     }
     if ($do_on_click_new!='run_method') {
      $action_object_new='';
      $action_method_new='';
     }
     if ($do_on_click_new!='open_menu') {
      $menu_item_id_new=0;
     }
     if ($do_on_click_new!='show_homepage') {
      $homepage_id_new=0;
     }
     if ($do_on_click_new!='show_url') {
      $ext_url_new='';
     }

     $state_rec['SCRIPT_ID']=$script_id_new;
     $state_rec['MENU_ITEM_ID']=$menu_item_id_new;
     $state_rec['ACTION_OBJECT']=$action_object_new;
     $state_rec['ACTION_METHOD']=$action_method_new;
     $state_rec['HOMEPAGE_ID']=(int)$homepage_id_new;
     $state_rec['EXT_URL']=$ext_url_new;

     if ($state_rec['CONDITION_ADVANCED']) {
       $errors=php_syntax_error($state_rec['CONDITION_ADVANCED']);
       if ($errors) {
        $state_rec['CONDITION_ADVANCED']='';;
       }
     }


     $state_rec['SWITCH_SCENE']=(int)$switch_scene_new;

     if ($state_rec['ID']) {
      SQLUpdate('elm_states', $state_rec);
     } else {
      $state_rec['ID']=SQLInsert('elm_states', $state_rec);
      $state_id=$state_rec['ID'];
     }
     
    } elseif ($element['TYPE']=='container') {

     $state_rec['TITLE']='default';
     $state_rec['ELEMENT_ID']=$element['ID'];
     $state_rec['TITLE']=$state_title_new;

     if ($state_rec['ID']) {
      SQLUpdate('elm_states', $state_rec);
     } else {
      $state_rec['ID']=SQLInsert('elm_states', $state_rec);
      $state_id=$state_rec['ID'];
     }

    } elseif (($element['TYPE']=='informer' || $element['TYPE']=='button' || $element['TYPE']=='nav') && !$state_rec['ID']) {

     $state_rec=array();
     $state_rec['TITLE']='default';
     $state_rec['ELEMENT_ID']=$element['ID'];
     $state_rec['HTML']=$element['TITLE'];
     $state_rec['ID']=SQLInsert('elm_states', $state_rec);
     $state_id=$state_rec['ID'];

    } elseif (($element['TYPE']=='switch') && !$state_rec['ID']) {

     global $linked_object;

     if (!$linked_object) {
      $linked_object='myObject';
     }

     $state_rec=array();
     $state_rec['TITLE']='off';
     $state_rec['HTML']=$element['TITLE'];
     $state_rec['ELEMENT_ID']=$element['ID'];
     $state_rec['IS_DYNAMIC']=1;
     $state_rec['LINKED_OBJECT']=$linked_object;
     $state_rec['LINKED_PROPERTY']='status';
     $state_rec['CONDITION']=4;
     $state_rec['CONDITION_VALUE']=1;
     $state_rec['ACTION_OBJECT']=$state_rec['LINKED_OBJECT'];
     $state_rec['ACTION_METHOD']='turnOn';
     $state_rec['ID']=SQLInsert('elm_states', $state_rec);


     $state_rec=array();
     $state_rec['TITLE']='on';
     $state_rec['HTML']=$element['TITLE'];
     $state_rec['ELEMENT_ID']=$element['ID'];
     $state_rec['IS_DYNAMIC']=1;
     $state_rec['LINKED_OBJECT']=$linked_object;
     $state_rec['LINKED_PROPERTY']='status';
     $state_rec['CONDITION']=1;
     $state_rec['CONDITION_VALUE']=1;
     $state_rec['ACTION_OBJECT']=$state_rec['LINKED_OBJECT'];
     $state_rec['ACTION_METHOD']='turnOff';
     $state_rec['ID']=SQLInsert('elm_states', $state_rec);
     $state_id=$state_rec['ID'];




    }


   }
        
   if (is_array($state_rec)) {
    foreach($state_rec as $k=>$v) {
     $out['STATE_'.$k]=htmlspecialchars($v);
    }
   }


   if (is_array($element)) {
    foreach ($element as $k=>$v) {
     $out['ELEMENT_'.$k]=htmlspecialchars($v);
    }
   }

  }


  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);

  if ($this->tab=='elements') {
   $out['HOMEPAGES']=SQLSelect("SELECT * FROM layouts ORDER BY TITLE");
   $out['SCRIPTS']=SQLSelect("SELECT * FROM scripts ORDER BY TITLE");
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
   $out['STATES']=SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='".$element['ID']."'");
   $out['STATE_ID']=$state_id;
  }

  //$elements=SQLSelect("SELECT `ID`, `SCENE_ID`, `TITLE`, `TYPE`, `TOP`, `LEFT`, `WIDTH`, `HEIGHT`, `CROSS_SCENE`, PRIORITY, (SELECT `IMAGE` FROM elm_states WHERE elements.ID = elm_states.element_ID LIMIT 1) AS `IMAGE` FROM elements WHERE SCENE_ID='".$rec['ID']."' ORDER BY PRIORITY DESC, TITLE");
  $elements=$this->getElements("SCENE_ID='".$rec['ID']."' AND CONTAINER_ID=0");
  $out['ELEMENTS']=$elements;

  $containers=SQLSelect("SELECT `ID`, `TITLE` FROM elements WHERE SCENE_ID='".$rec['ID']."' AND TYPE='container' ORDER BY PRIORITY DESC, TITLE");
  $out['CONTAINERS']=$containers;

  $out['SCENES']=SQLSelect("SELECT * FROM scenes ORDER BY TITLE");


?>