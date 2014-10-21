<?php
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

  if (preg_match('/(\d+)\.html/', $_SERVER["REQUEST_URI"], $m)) {
   $qry.=" AND scenes.ID='".$m[1]."'";
  } else {
   $qry.=" AND scenes.HIDDEN!=1";
  }

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
  if ($this->action!='admin') {
   $total=count($res);
   $res2=array();
   for($i=0;$i<$total;$i++) {
    if (checkAccess('scene', $res[$i]['ID'])) {
     $res2[]=$res[$i];
    }
   }
   $res=$res2;
   unset($res2);
  }

  if ($res[0]['ID']) {
   $total=count($res);
   $positions=array();
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
      $elements=SQLSelect("SELECT * FROM elements WHERE SCENE_ID='".$res[$i]['ID']."' ORDER BY PRIORITY DESC, TITLE");
      $totale=count($elements);
      for($ie=0;$ie<$totale;$ie++) {
       if ($elements[$ie]['PRIORITY']) {
        $elements[$ie]['ZINDEX']=$elements[$ie]['PRIORITY']*10;
       }
       $positions[$elements[$ie]['ID']]['TOP']=$elements[$ie]['TOP'];
       $positions[$elements[$ie]['ID']]['LEFT']=$elements[$ie]['LEFT'];
       $states=SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='".$elements[$ie]['ID']."' ORDER BY PRIORITY DESC, TITLE");
       $elements[$ie]['STATES']=$states;
      }
      for($ie=0;$ie<$totale;$ie++) {
       if ($elements[$ie]['LINKED_ELEMENT_ID']) {
        $elements[$ie]['TOP']=$positions[$elements[$ie]['LINKED_ELEMENT_ID']]['TOP']+$elements[$ie]['TOP'];
        $elements[$ie]['LEFT']=$positions[$elements[$ie]['LINKED_ELEMENT_ID']]['LEFT']+$elements[$ie]['LEFT'];
        $positions[$elements[$ie]['ID']]['TOP']=$elements[$ie]['TOP'];
        $positions[$elements[$ie]['ID']]['LEFT']=$elements[$ie]['LEFT'];
       }
      }
      $res[$i]['ELEMENTS']=$elements;
      $res[$i]['NUM']=$i;
      $res[$i]['NUMP']=$i+1;
   }
   $out['TOTAL']=$total;
   $out['RESULT']=$res;
  }

?>