<?php

/*
* @version 0.1 (auto-set)
*/

global $session;

if($this->owner->name == 'panel') {
	$out['CONTROLPANEL'] = 1;
}
$qry = '1';

// search filters
// QUERY READY
global $save_qry;
if($save_qry) {
	$qry = $session->data['terminals_qry'];
} else {
	$session->data['terminals_qry'] = $qry;
}
if(!$qry) $qry = '1';

// FIELDS ORDER
global $sortby;
if(!$sortby) {
	$sortby = $session->data['terminals_sort'];
} else {
	if($session->data['terminals_sort'] == $sortby) {
		if(is_integer(strpos($sortby, ' DESC'))) {
			$sortby = str_replace(' DESC', '', $sortby);
		} else {
			$sortby=$sortby." DESC";
		}
	}
	$session->data['terminals_sort'] = $sortby;
}
if(!$sortby) $sortby = "TITLE";
$out['SORTBY'] = $sortby;

// SEARCH RESULTS
$res = SQLSelect("SELECT * FROM `terminals` WHERE $qry ORDER BY $sortby");
if($res[0]['ID']) {
	paging($res, 50, $out); // search result paging
	colorizeArray($res);
	$total = count($res);
	for($i=0;$i<$total;$i++) {
		if (!checkAccess('terminal', $res[$i]['ID'])) {
			continue;// some action for every record if required
		}
		$out['RESULT'][] = $res[$i];
	}
}

?>
