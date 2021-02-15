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

  $recently=gr('recently_updated','int');
  if ($recently) {
   $this->redirect("?view_mode=edit_scripts&id=".$recently);
  }

  global $title;
  if ($title!='') {
   $qry.=" AND (scripts.TITLE LIKE '%".DBSafe($title)."%' OR scripts.DESCRIPTION LIKE '%".DBSafe($title)."%' OR scripts.CODE LIKE '%".DBSafe($title)."%')";
   $out['TITLE']=$title;
   $out['ALL_OPEN']=1;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['scripts_qry'];
  } else {
   $session->data['scripts_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['scripts_sort'];
  } else {
   if ($session->data['scripts_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['scripts_sort']=$sortby;
  }
  $sortby="script_categories.TITLE, scripts.TITLE";
  $out['SORTBY']=$sortby;
  $out['TOTAL_CATEGORIES']=0;
  // SEARCH RESULTS
//echo $qry;exit;
  $res=SQLSelect("SELECT scripts.*, script_categories.TITLE as CATEGORY FROM scripts LEFT JOIN script_categories ON scripts.CATEGORY_ID=script_categories.ID WHERE $qry ORDER BY $sortby");
  $old_category='';
  if ($res[0]['ID']) {
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    if (!$res[$i]['CATEGORY']) {
     $res[$i]['CATEGORY']=LANG_OTHER;
    }
    $res[$i]['DESCRIPTION']=nl2br(htmlspecialchars($res[$i]['DESCRIPTION']));

    $executed_tm=strtotime($res[$i]['EXECUTED']);
    if ($executed_tm>0) {
     $res[$i]['EXECUTED_PASSED']=getPassedText($executed_tm);
    }

    if ($res[$i]['CATEGORY']!=$old_category) {
     $out['TOTAL_CATEGORIES']++;
     $old_category=$res[$i]['CATEGORY'];
     $res[$i]['NEW_CATEGORY']=1;
    }
	
    if ($i==$total-1) {
     $res[$i]['LAST']=1;
    }

   }
   $res[0]['FIRST']=1;
   $out['RESULT']=$res;
  }


$recently_updated = SQLSelect("SELECT ID, TITLE, UPDATED FROM scripts ORDER BY UPDATED DESC, ID DESC LIMIT 20");
if ($recently_updated[0]['ID']) {
 $total = count($recently_updated);
 for($i=0;$i<$total;$i++) {
  if ($recently_updated[$i]['UPDATED']) {
   $recently_updated[$i]['PASSED']=getPassedText(strtotime($recently_updated[$i]['UPDATED']));
  } else {
   $recently_updated[$i]['PASSED']='...';
  }
 }
 $out['RECENTLY_UPDATED']=$recently_updated;
}