<?
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
  //updating 'PRIORITY' (int)
   global $priority;
   $rec['PRIORITY']=(int)$priority;
  }
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
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

   if ($state_id) {
    $state_rec=SQLSelectOne("SELECT * FROM elm_states WHERE ID='".$state_id."'");
    if (!$rec['ID']) {
     $state_id='';
    }
   } else {
    $state_rec=SQLSelectOne("SELECT * FROM elm_states WHERE ELEMENT_ID='".$element_id."' ORDER BY ID");
    $state_id=$state_rec['ID'];
   }


   if ($this->mode=='update') {
    $ok=1;
    global $title;
    $element['TITLE']=$title;
    if (!$element['TITLE']) {
     $ok=0;
     $out['ERR_TITLE']=1;
    }
    global $top;
    $element['TOP']=(int)$top;

    global $left;
    $element['LEFT']=(int)$left;

    global $type;
    $element['TYPE']=$type;

    global $height;
    $element['HEIGHT']=(int)$height;

    global $width;
    $element['WIDTH']=(int)$width;

    global $cross_scene;
    $element['CROSS_SCENE']=(int)$cross_scene;

    $element['SCENE_ID']=$rec['ID'];

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
    global $is_dynamic_new;
    global $linked_object_new;
    global $linked_property_new;
    global $condition_new;
    global $condition_value_new;
    global $condition_advanced_new;
    global $switch_scene_new;
    global $state_id;
    global $state_delete;

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
     $state_rec['SCRIPT_ID']=$script_id_new;
     $state_rec['IS_DYNAMIC']=$is_dynamic_new;
     $state_rec['LINKED_OBJECT']=$linked_object_new;
     $state_rec['LINKED_PROPERTY']=$linked_property_new;
     $state_rec['CONDITION']=$condition_new;
     $state_rec['CONDITION_VALUE']=$condition_value_new;
     $state_rec['CONDITION_ADVANCED']=$condition_advanced_new;

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
     
    }                           


   }

   if (is_array($state_rec)) {
    foreach($state_rec as $k=>$v) {
     $out['STATE_'.$k]=$v;
    }
   }


   if (is_array($element)) {
    foreach ($element as $k=>$v) {
     $out['ELEMENT_'.$k]=$v;
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
   $out['SCRIPTS']=SQLSelect("SELECT * FROM scripts ORDER BY TITLE");
   $out['STATES']=SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='".$element['ID']."'");
   $out['STATE_ID']=$state_id;
  }

  $elements=SQLSelect("SELECT * FROM elements WHERE SCENE_ID='".$rec['ID']."'");
  $out['ELEMENTS']=$elements;


?>