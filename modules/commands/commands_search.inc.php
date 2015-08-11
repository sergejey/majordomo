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
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['commands_sort'];
  } else {
   if ($session->data['commands_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['commands_sort']=$sortby;
  }
  $sortby="PRIORITY DESC, TITLE";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS

  $res=SQLSelect("SELECT * FROM commands WHERE $qry ORDER BY $sortby");

   if ($res[0]['ID']) {

    if ($this->action!='admin') {
     $dynamic_res=array();
     $total=count($res);
     for($i=0;$i<$total;$i++) {
      if ($res[$i]['SMART_REPEAT'] && $res[$i]['LINKED_OBJECT']) {
       $obj=getObject($res[$i]['LINKED_OBJECT']);
       $objects=getObjectsByClass($obj->class_id);
       $total_o=count($objects);
       for($io=0;$io<$total_o;$io++) {
        $rec=$res[$i];
        $rec['ID']=$res[$i]['ID'].'_'.$objects[$io]['ID'];
        $rec['LINKED_OBJECT']=$objects[$io]['TITLE'];
        $rec['DATA']=str_replace('%'.$res[$i]['LINKED_OBJECT'].'.', '%'.$rec['LINKED_OBJECT'].'.', $rec['DATA']);
        $rec['CUR_VALUE']=getGlobal($rec['LINKED_OBJECT'].'.'.$rec['LINKED_PROPERTY']);
        $rec['TITLE']=$objects[$io]['TITLE'];
        $dynamic_res[]=$rec;
       }
      } else {
       $dynamic_res[]=$res[$i];
      }
     }
     $res=$dynamic_res;
    }

    $this->processMenuElements($res);
    if ($this->action=='admin') {
     $res=$this->buildTree_commands($res);
    }
    $out['RESULT']=$res;
    //$out['RESULT_HTML']=$this->buildHTML($out['RESULT']);
   }


   
   
?>