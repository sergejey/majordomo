<?
/*
* @version 0.1 (auto-set)
*/

 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['classes_qry'];
  } else {
   $session->data['classes_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['classes_sort'];
  } else {
   if ($session->data['classes_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['classes_sort']=$sortby;
  }
  if (!$sortby) $sortby="TITLE";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM classes WHERE $qry ORDER BY $sortby");
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $objects=SQLSelect("SELECT ID, TITLE, DESCRIPTION FROM objects WHERE CLASS_ID='".$res[$i]['ID']."'");
    if ($objects[0]['ID']) {
     $total_o=count($objects);
     for($o=0;$o<$total_o;$o++) {
      $methods=SQLSelect("SELECT ID, TITLE FROM methods WHERE OBJECT_ID='".$objects[$o]['ID']."'");
      if ($methods[0]['ID']) {
       $objects[$o]['METHODS']=$methods;
      }
     }
     $res[$i]['OBJECTS']=$objects;
    }
   }
   $res=$this->buildTree_classes($res);
   $out['RESULT']=$res;
  }
?>