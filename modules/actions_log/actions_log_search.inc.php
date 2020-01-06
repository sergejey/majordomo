<?php
/*
* @version 0.1 (wizard)
*/
global $session;
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$qry = "1";
// search filters
$from = gr('from');
if ($from) {
    $out['FROM'] = $from;
    $out['FROM_URL'] = urlencode($from);
    $qry .= " AND actions_log.ADDED>='" . $from . "'";
}

$to = gr('to');
if ($to) {
    $out['TO'] = $to;
    $out['TO_URL'] = urlencode($to);
    $qry .= " AND actions_log.ADDED<='" . $to . "'";
}

$action_type = gr('action_type');
if ($action_type) {
    $qry .= " AND actions_log.ACTION_TYPE='" . $action_type . "'";
    $out['ACTION_TYPE'] = $action_type;
}

$search = gr('search');
if ($search != '') {
    if (preg_match('/\d\.\d\.\d\.\d/', $search)) {
        $qry .= " AND actions_log.IP='" . $search . "'";
    } else {
        $qry .= " AND (actions_log.TITLE LIKE '%" . DBSafe($search) . "%'";
    }
    $out['SEARCH'] = htmlspecialchars($search);
    $out['SEARCH_URL'] = urlencode($search);
}

// QUERY READY
global $save_qry;
if ($save_qry) {
    $qry = $session->data['actions_log_qry'];
} else {
    $session->data['actions_log_qry'] = $qry;
}
if (!$qry) $qry = "1";
$sortby_actions_log = "actions_log.ID DESC";
$out['SORTBY'] = $sortby_actions_log;


// SEARCH RESULTS
$res_total = SQLSelectOne("SELECT count(*) as TOTAL FROM actions_log WHERE $qry");
$page = gr('page', 'int');
if (!$page) $page = 1;
$out['PAGE'] = $page;
$on_page = 50;
$limit = (($page - 1) * $on_page) . ',' . $on_page;
$urlPattern = '?page=(:num)&search=' . urlencode($search) . "&from=" . urlencode($from) . "&to=" . urlencode($to) . "&action_type=" . urlencode($action_type);
require(ROOT . '3rdparty/Paginator/Paginator.php');
$paginator = new JasonGrimes\Paginator($res_total['TOTAL'], $on_page, $page, $urlPattern);
$out['PAGINATOR'] = $paginator;
$res = SQLSelect("SELECT actions_log.* FROM actions_log WHERE $qry ORDER BY " . $sortby_actions_log . " LIMIT $limit");


if ($res[0]['ID']) {
    //paging($res, 100, $out); // search result paging
    $total = count($res);
    for ($i = 0; $i < $total; $i++) {
        // some action for every record if required
    }
    $out['RESULT'] = $res;
}

$out['ACTION_TYPES'] = SQLSelect("SELECT DISTINCT(ACTION_TYPE) FROM actions_log ORDER BY ACTION_TYPE");