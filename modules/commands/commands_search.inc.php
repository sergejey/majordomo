<?
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
  }

  if ($this->parent_item!='') {
   $qry.=" AND PARENT_ID='".$this->parent_item."'";
   $parent_rec=SQLSelectOne("SELECT * FROM commands WHERE ID='".$this->parent_item."'");
   $parent_rec['TITLE']=processTitle($parent_rec['TITLE'], $this);
   foreach($parent_rec as $k=>$v) {
    $out['PARENT_'.$k]=$v;
   }
  } elseif ($this->id) {
   $qry.=" AND ID=".(int)$this->id;
   $out['ONE_ITEM_MODE']=1;
   $this->pda=1;
   $out['PDA']=1;
  } else {
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
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required

   $item=$res[$i];

   if ($item['LINKED_PROPERTY']!='') {
    $lprop=getObject($item['LINKED_OBJECT'])->getProperty($item['LINKED_PROPERTY']);
    if ($item['TYPE']=='custom') {
     $field='DATA';
    } else {
     $field='CUR_VALUE';
    }
    if ($lprop!=$item[$field]) {
     $item[$field]=$lprop;
     SQLUpdate('commands', $item);
     $res[$i]=$item;
    }
   }

   if ($item['TYPE']=='timebox') {

    $tmp=explode(':', $item['CUR_VALUE']);
    $value1=(int)$tmp[0];
    $value2=(int)$tmp[1];

    for($h=0;$h<=23;$h++) {
     $v=$h;
     if ($v<10) {
      $v='0'.$v;
     }
     $selected=0;
     if ($h==$value1) {
      $selected=1;
     }
     $item['OPTIONS1'][]=array('VALUE'=>$v, 'SELECTED'=>$selected);
    }
    for($h=0;$h<=59;$h++) {
     $v=$h;
     if ($v<10) {
      $v='0'.$v;
     }
     $selected=0;
     if ($h==$value2) {
      $selected=1;
     }
     $item['OPTIONS2'][]=array('VALUE'=>$v, 'SELECTED'=>$selected);
    }
    $res[$i]=$item;
       //print_r($item);exit;
   }


   if ($item['TYPE']=='selectbox') {
    $data=explode("\n", str_replace("\r", "", $item['DATA']));
    $item['OPTIONS']=array();
    foreach($data as $line) {
     $line=trim($line);
     if ($line!='') {
      $option=array();
      $tmp=explode('|', $line);
      $option['VALUE']=$tmp[0];
      if ($tmp[1]!='') {
       $option['TITLE']=$tmp[1];
      } else {
       $option['TITLE']=$option['VALUE'];
      }
      if ($option['VALUE']==$item['CUR_VALUE']) {
       $option['SELECTED']=1;
      }
      $item['OPTIONS'][]=$option;
     }
    }
    $res[$i]=$item;
   }

   if ($this->owner->name!='panel') {
    $res[$i]['TITLE']=processTitle($res[$i]['TITLE'], $this);
    if ($res[$i]['TYPE']=='custom') {
     $res[$i]['DATA']=processTitle($res[$i]['DATA'], $this);
    }
   }


    foreach($res[$i] as $k=>$v) {
     if (!is_array($res[$i][$k]) && $k!='DATA') {
      $res[$i][$k]=addslashes($v);
     }
    }

    $tmp=SQLSelectOne("SELECT COUNT(*) as TOTAL FROM commands WHERE PARENT_ID='".$res[$i]['ID']."'");
    if ($tmp['TOTAL']) {
     $res[$i]['RESULT']=$tmp['TOTAL'];
    }
   }

   if ($this->action=='admin') {
    $res=$this->buildTree_commands($res);
   }

  $out['RESULT']=$res;
   
   
  }
?>