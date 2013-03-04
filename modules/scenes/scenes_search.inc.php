<?
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['scenes_qry'];
  } else {
   $session->data['scenes_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_scenes;
  if (!$sortby_scenes) {
   $sortby_scenes=$session->data['scenes_sort'];
  } else {
   if ($session->data['scenes_sort']==$sortby_scenes) {
    if (Is_Integer(strpos($sortby_scenes, ' DESC'))) {
     $sortby_scenes=str_replace(' DESC', '', $sortby_scenes);
    } else {
     $sortby_scenes=$sortby_scenes." DESC";
    }
   }
   $session->data['scenes_sort']=$sortby_scenes;
  }
  if (!$sortby_scenes) $sortby_scenes="PRIORITY DESC, TITLE";
  $out['SORTBY']=$sortby_scenes;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM scenes WHERE $qry ORDER BY ".$sortby_scenes);
  if ($res[0]['ID']) {
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
      $elements=SQLSelect("SELECT * FROM elements WHERE SCENE_ID='".$res[$i]['ID']."'");
      $totale=count($elements);
      for($ie=0;$ie<$totale;$ie++) {
       $states=SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='".$elements[$ie]['ID']."'");
       $elements[$ie]['STATES']=$states;
      }
      $res[$i]['ELEMENTS']=$elements;
      $res[$i]['NUM']=$i;
      $res[$i]['NUMP']=$i+1;
   }
   $out['RESULT']=$res;
  }

?>