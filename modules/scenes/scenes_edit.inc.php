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
   $rec['BACKGROUND']=$background;

   global $wallpaper;
   $rec['WALLPAPER']=$wallpaper;

   global $wallpaper_fixed;
   $rec['WALLPAPER_FIXED']=(int)$wallpaper_fixed;

   global $wallpaper_norepeat;
   $rec['WALLPAPER_NOREPEAT']=(int)$wallpaper_norepeat;


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

  if ($view_mode2=='up_elements') {
   global $element_id;
   $this->reorder_elements($element_id, 'up');
   $this->redirect("?id=".$rec['ID']."&view_mode=".$this->view_mode."&tab=".$this->tab);
  }

  if ($view_mode2=='down_elements') {
   global $element_id;
   $this->reorder_elements($element_id, 'down');
   $this->redirect("?id=".$rec['ID']."&view_mode=".$this->view_mode."&tab=".$this->tab);
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

    global $smart_repeat;
    $element['SMART_REPEAT']=(int)$smart_repeat;


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
    global $open_scene_id_new;
    global $do_on_click_new;
    global $priority_new;
    global $code_new;

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
     $state_rec['PRIORITY']=(int)$priority_new;

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

     if ($do_on_click_new!='show_scene') {
      $open_scene_id_new=0;
     }

     if ($do_on_click_new!='show_url') {
      $ext_url_new='';
     }
     if ($do_on_click_new!='run_code') {
      $code_new='';
     }

     $state_rec['CODE']=$code_new;
     $state_rec['SCRIPT_ID']=$script_id_new;
     $state_rec['MENU_ITEM_ID']=$menu_item_id_new;
     $state_rec['ACTION_OBJECT']=$action_object_new;
     $state_rec['ACTION_METHOD']=$action_method_new;
     $state_rec['HOMEPAGE_ID']=(int)$homepage_id_new;
     $state_rec['OPEN_SCENE_ID']=(int)$open_scene_id_new;
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

    } elseif (($element['TYPE']=='nav' || $element['TYPE']=='navgo') && !$state_rec['ID']) {

     $state_rec=array();
     $state_rec['TITLE']='default';
     $state_rec['ELEMENT_ID']=$element['ID'];
     $state_rec['HTML']=$element['TITLE'];
     $state_rec['ID']=SQLInsert('elm_states', $state_rec);
     $state_id=$state_rec['ID'];

    } elseif ($element['TYPE']=='button' && !$state_rec['ID']) {

     global $linked_object;
     global $linked_method;

     $state_rec=array();
     $state_rec['TITLE']='default';
     $state_rec['ELEMENT_ID']=$element['ID'];
     $state_rec['HTML']=$element['TITLE'];

     if ($linked_object && $linked_method) {
       $state_rec['ACTION_OBJECT']=$linked_object;
       $state_rec['ACTION_METHOD']=$linked_method;
     }

     $state_rec['ID']=SQLInsert('elm_states', $state_rec);
     $state_id=$state_rec['ID'];


    } elseif (($element['TYPE']=='warning') && !$state_rec['ID']) {
     global $linked_object;
     global $linked_property;
     $state_rec=array();
     $state_rec['TITLE']='default';
     $state_rec['ELEMENT_ID']=$element['ID'];
     $state_rec['HTML']=$element['TITLE'].'<br/>detected';
     $state_rec['LINKED_OBJECT']=$linked_object;
     if (!$linked_property) {
      $linked_property='motionDetected';
     }
     $state_rec['LINKED_PROPERTY']=$linked_property;
     $state_rec['IS_DYNAMIC']=1;
     $state_rec['CONDITION']=1;
     $state_rec['CONDITION_VALUE']=1;
     $state_rec['ID']=SQLInsert('elm_states', $state_rec);
     $state_id=$state_rec['ID'];


    } elseif (($element['TYPE']=='informer') && !$state_rec['ID']) {
     global $linked_object;
     global $linked_property;
     global $state_high;
     global $state_high_value;
     global $state_low;
     global $state_low_value;
     global $linked_property_unit;


     if ($state_low_value!='' && !is_numeric($state_low_value) && !preg_match('/^%/', $state_low_value)) {
      $state_low_value='%'.$state_low_value.'%';
     }
     if ($state_high_value!='' && !is_numeric($state_high_value) && !preg_match('/^%/', $state_high_value)) {
      $state_high_value='%'.$state_high_value.'%';
     }



     if ($state_high) {
      $state_rec=array();
      $state_rec['TITLE']='high';
      $state_rec['ELEMENT_ID']=$element['ID'];
      $state_rec['HTML']='%'.$linked_object.'.'.$linked_property.'%';
      if ($linked_property_unit) {
       $state_rec['HTML'].=' '.$linked_property_unit;
      }
      $state_rec['LINKED_OBJECT']=$linked_object;
      $state_rec['LINKED_PROPERTY']=$linked_property;
      $state_rec['IS_DYNAMIC']=1;
      if ($state_high_value) {
       $state_rec['CONDITION']=2;
       $state_rec['CONDITION_VALUE']=$state_high_value;
      }
      $state_rec['ID']=SQLInsert('elm_states', $state_rec);
     }

     if ($state_low) {
      $state_rec=array();
      $state_rec['TITLE']='low';
      $state_rec['ELEMENT_ID']=$element['ID'];
      $state_rec['HTML']='%'.$linked_object.'.'.$linked_property.'%';
      if ($linked_property_unit) {
       $state_rec['HTML'].=' '.$linked_property_unit;
      }
      $state_rec['LINKED_OBJECT']=$linked_object;
      $state_rec['LINKED_PROPERTY']=$linked_property;
      $state_rec['IS_DYNAMIC']=1;
      if ($state_low_value) {
       $state_rec['CONDITION']=3;
       $state_rec['CONDITION_VALUE']=$state_low_value;
      }
      $state_rec['ID']=SQLInsert('elm_states', $state_rec);
     }

     $state_rec=array();
     $state_rec['TITLE']='default';
     $state_rec['ELEMENT_ID']=$element['ID'];
     $state_rec['HTML']='%'.$linked_object.'.'.$linked_property.'%';
     if ($linked_property_unit) {
      $state_rec['HTML'].=' '.$linked_property_unit;
     }
     if ($state_high || $state_low) {
      $state_rec['IS_DYNAMIC']=1;
      $state_rec['LINKED_OBJECT']=$linked_object;
      $state_rec['LINKED_PROPERTY']=$linked_property;
      //is_dynamic 2
      if ($state_high && $state_low) {
       $state_rec['IS_DYNAMIC']=2;
       $state_rec['CONDITION_ADVANCED']='if (gg(\''.$linked_object.'.'.$linked_property.'\')>=(float)\''.$state_low_value.'\' && gg(\''.$linked_object.'.'.$linked_property.'\')<=(float)\''.$state_high_value.'\') {'."\n ".'$display=1;'."\n".'} else {'."\n ".'$display=0;'."\n".'}';
      } elseif ($state_high) {
       $state_rec['IS_DYNAMIC']=1;
       $state_rec['CONDITION']=3;
       $state_rec['CONDITION_VALUE']=$state_high_value;
      } elseif ($state_low) {
       $state_rec['IS_DYNAMIC']=1;
       $state_rec['CONDITION']=2;
       $state_rec['CONDITION_VALUE']=$state_low_value;
      }
     }
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


    } elseif (($element['TYPE']=='mode') && !$state_rec['ID']) {

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
     $state_rec['LINKED_PROPERTY']='active';
     $state_rec['CONDITION']=4;
     $state_rec['CONDITION_VALUE']=1;
     $state_rec['ACTION_OBJECT']=$state_rec['LINKED_OBJECT'];
     $state_rec['ACTION_METHOD']='activate';
     $state_rec['ID']=SQLInsert('elm_states', $state_rec);


     $state_rec=array();
     $state_rec['TITLE']='on';
     $state_rec['HTML']=$element['TITLE'];
     $state_rec['ELEMENT_ID']=$element['ID'];
     $state_rec['IS_DYNAMIC']=1;
     $state_rec['LINKED_OBJECT']=$linked_object;
     $state_rec['LINKED_PROPERTY']='active';
     $state_rec['CONDITION']=1;
     $state_rec['CONDITION_VALUE']=1;
     $state_rec['ACTION_OBJECT']=$state_rec['LINKED_OBJECT'];
     $state_rec['ACTION_METHOD']='deactivate';
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
    if ($element['CSS_STYLE']!='default') {
     $out['ELEMENT_CSS_IMAGE']=$this->getCSSImage($element['TYPE'], $element['CSS_STYLE']);
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

  if ($element['TYPE']) {
   $styles=$this->getStyles($element['TYPE']);
   if (is_array($styles)) {
    $out['STYLES']=$styles;
   }

   $styles=$this->getStyles('common');
   if (is_array($styles)) {
    $out['COMMON_STYLES']=$styles;
   }

  }


  if ($this->tab=='elements') {
   $out['OTHER_SCENES']=SQLSelect("SELECT ID, TITLE FROM scenes ORDER BY PRIORITY DESC, TITLE");
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
   $out['STATES']=SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='".$element['ID']."' ORDER BY elm_states.PRIORITY DESC");
   $out['STATE_ID']=$state_id;
  }

  //$elements=SQLSelect("SELECT `ID`, `SCENE_ID`, `TITLE`, `TYPE`, `TOP`, `LEFT`, `WIDTH`, `HEIGHT`, `CROSS_SCENE`, PRIORITY, (SELECT `IMAGE` FROM elm_states WHERE elements.ID = elm_states.element_ID LIMIT 1) AS `IMAGE` FROM elements WHERE SCENE_ID='".$rec['ID']."' ORDER BY PRIORITY DESC, TITLE");
  $elements=$this->getElements("SCENE_ID='".$rec['ID']."' AND CONTAINER_ID=0");
  if (count($elements)) {
  /*
   $total=count($elements);
   for($i=0;$i<$total;$i++) {
     if ($elements[$i]['CSS_STYLE']!='default' && $elements[$i]['CSS_STYLE']!='') {
      $elements[$i]['CSS_IMAGE']=$this->getCSSImage($elements[$i]['TYPE'], $elements[$i]['CSS_STYLE']);
     }
   }
   */
   $out['ELEMENTS']=$elements;
  }

  if ($element['TYPE']=='container') {
   $sub_elements=SQLSelect("SELECT ID, TITLE FROM elements WHERE CONTAINER_ID=".(int)$element['ID']." ORDER BY PRIORITY DESC, TITLE");
  } elseif ($element['ID']) {
   $sub_elements=SQLSelect("SELECT ID, TITLE FROM elements WHERE CONTAINER_ID=".(int)$element['CONTAINER_ID']." AND SCENE_ID='".$rec['ID']."' ORDER BY PRIORITY DESC, TITLE");
  }
  if ($sub_elements[0]['ID']) {
   $out['SUB_ELEMENTS']=$sub_elements;
  }



  $containers=SQLSelect("SELECT `ID`, `TITLE` FROM elements WHERE SCENE_ID='".$rec['ID']."' AND TYPE='container' ORDER BY PRIORITY DESC, TITLE");
  if ($element['CONTAINER_ID']) {
   $total=count($containers);
   for($i=0;$i<$total;$i++) {
    if ($containers[$i]['ID']==$element['CONTAINER_ID']) {
     $out['CURRENT_CONTAINER_TITLE']=$containers[$i]['TITLE'];
    }
   }
  }



  $out['CONTAINERS']=$containers;

  $out['SCENES']=SQLSelect("SELECT * FROM scenes ORDER BY TITLE");




?>