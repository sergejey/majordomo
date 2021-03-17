<?php
/*
* @version 0.4 (auto-set)
*/

 global $menu_loaded;

 $out['MENU_LOADED']=$menu_loaded;

 $menu_loaded=1;

 if ($this->pda) {
  $out['PDA']=1;
 }

 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }

  $qry="1";
  // search filters

  if ($this->action!='admin') {

  if ($this->owner->parent_item) {
   $this->parent_item=$this->owner->parent_item;
   //$_GET['parent']=$this->parent_item;
  }

  if ($_GET['from_scene']) {
   $out['FROM_SCENE']=1;
   $out['ONE_ITEM_MODE']=1;
  }

  if ($_GET['parent']) {
   $this->parent_item=$_GET['parent'];
   /*
   $tmp=SQLSelectOne("SELECT ID FROM commands WHERE PARENT_ID='".$_GET['parent']."'");
   if ($tmp['ID']) {
    $qry.=" AND (commands.PARENT_ID='".$_GET['parent']."')";
   } else {
    $qry.=" AND (commands.ID='".(int)$_GET['parent']."')";
   }
   */
   $out['IFRAME_MODE']=1;
  }


  if ($this->parent_item!='') {
   //$out['IFRAME_MODE']=1;
   $tmp=SQLSelectOne("SELECT ID FROM commands WHERE PARENT_ID='".$_GET['parent']."'");

   if ($tmp['ID']) {
    $qry.=" AND (commands.PARENT_ID='".$this->parent_item."')";
   } else {
    $qry.=" AND (commands.ID='".$this->parent_item."')";
   }

   //$qry.=" AND PARENT_ID='".$this->parent_item."'";
   $parent_rec=SQLSelectOne("SELECT * FROM commands WHERE ID='".$this->parent_item."'");
   $parent_rec['TITLE']=processTitle($parent_rec['TITLE'], $this);
   if ($paret_rec['SUB_PRELOAD']) {
    $parent_rec['ID']=$parent_rec['PARENT_ID'];
   }
 
   if (!$parent_rec['AUTO_UPDATE'] && (!defined('DISABLE_WEBSOCKETS') || DISABLE_WEBSOCKETS==0)) {
     $parent_rec['AUTO_UPDATE']=10;
    }

   foreach($parent_rec as $k=>$v) {
    $out['PARENT_'.$k]=$v;
   }

  } elseif ($this->id) {
   $qry.=" AND ID=".(int)$this->id;
   $out['ONE_ITEM_MODE']=1;
   $this->pda=1;
   $out['PDA']=1;
  } elseif (!$_GET['parent']) {
   $qry.=" AND PARENT_ID=0";
  }
  }

  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['commands_qry'];
  } else {
   $session->data['commands_qry']=$qry;
  }
  if (!$qry) $qry="1";

  // SEARCH RESULTS

  if ($this->action!='admin') {
   $res=$this->getDynamicElements($qry);
  } else {

   $go_linked_object=gr('go_linked_object');
   $go_linked_property=gr('go_linked_property');
   if ($go_linked_object && $go_linked_property) {
    $tmp = SQLSelectOne("SELECT ID FROM commands WHERE LINKED_OBJECT = '".DBSafe($go_linked_object)."' AND LINKED_PROPERTY='".DBSafe($go_linked_property)."'");
    if ($tmp['ID']) {
     $this->redirect("?id=".$tmp['ID']."&view_mode=edit_commands");
    }
   }

   $res=SQLSelect("SELECT * FROM commands WHERE $qry ORDER BY PRIORITY DESC, TITLE");
  }

   if ($res[0]['ID']) {

    $this->processMenuElements($res);
    if ($this->action=='admin') {
     $res=$this->buildTree_commands($res);
    }
    $out['RESULT']=$res;
    //$out['RESULT_HTML']=$this->buildHTML($out['RESULT']);
   }

