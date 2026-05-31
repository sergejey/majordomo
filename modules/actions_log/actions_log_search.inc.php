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
$period = trim(gr('period'));
if ($period == '') {
    $period = 'custom';
}
$out['PERIOD'] = $period;

$from = trim(gr('from'));
$to = trim(gr('to'));
if ($period != 'custom') {
    $period_to = date('Y-m-d H:i:s');
    if ($period == 'today') {
        $period_from = date('Y-m-d 00:00:00');
    } elseif ($period == 'day') {
        $period_from = date('Y-m-d H:i:s', time() - 24 * 60 * 60);
    } elseif ($period == 'week') {
        $period_from = date('Y-m-d H:i:s', time() - 7 * 24 * 60 * 60);
    } elseif ($period == 'month') {
        $period_from = date('Y-m-d H:i:s', time() - 30 * 24 * 60 * 60);
    } else {
        $period_from = '';
    }
    if ($period_from != '') {
        $from = $period_from;
        $to = $period_to;
    }
}

if ($from != '') {
    $out['FROM'] = $from;
    $qry .= " AND actions_log.ADDED>='" . DBSafe($from) . "'";
}

if ($to != '') {
    $out['TO'] = $to;
    $qry .= " AND actions_log.ADDED<='" . DBSafe($to) . "'";
}

$action_type = trim(gr('action_type'));
if ($action_type != '') {
    $qry .= " AND actions_log.ACTION_TYPE='" . DBSafe($action_type) . "'";
    $out['ACTION_TYPE'] = $action_type;
}

$source = trim(gr('source'));
if ($source != '') {
    $qry .= " AND actions_log.SOURCE='" . DBSafe($source) . "'";
    $out['SOURCE'] = $source;
}

$module_name = trim(gr('module_name'));
if ($module_name != '') {
    $qry .= " AND actions_log.MODULE='" . DBSafe($module_name) . "'";
    $out['MODULE_NAME'] = $module_name;
}

$result = trim(gr('result'));
if ($result != '') {
    $qry .= " AND actions_log.RESULT='" . DBSafe($result) . "'";
    $out['RESULT_FILTER'] = $result;
}

$request_method = trim(gr('request_method'));
if ($request_method != '') {
    $qry .= " AND actions_log.REQUEST_METHOD='" . DBSafe($request_method) . "'";
    $out['REQUEST_METHOD'] = $request_method;
}

$user_name = trim(gr('user_name'));
if ($user_name != '') {
    $qry .= " AND actions_log.USER LIKE '%" . DBSafe($user_name) . "%'";
    $out['USER_NAME'] = htmlspecialchars($user_name, ENT_QUOTES);
}

$terminal = trim(gr('terminal'));
if ($terminal != '') {
    $qry .= " AND actions_log.TERMINAL LIKE '%" . DBSafe($terminal) . "%'";
    $out['TERMINAL_FILTER'] = htmlspecialchars($terminal, ENT_QUOTES);
}

$ip = trim(gr('ip'));
if ($ip != '') {
    $qry .= " AND actions_log.IP LIKE '%" . DBSafe($ip) . "%'";
    $out['IP_FILTER'] = htmlspecialchars($ip, ENT_QUOTES);
}

$search = trim(gr('search'));
if ($search != '') {
    $safe_search = DBSafe($search);
    $search_conditions = array();
    $search_conditions[] = "actions_log.TITLE LIKE '%" . $safe_search . "%'";
    $search_conditions[] = "actions_log.USER LIKE '%" . $safe_search . "%'";
    $search_conditions[] = "actions_log.TERMINAL LIKE '%" . $safe_search . "%'";
    $search_conditions[] = "actions_log.IP LIKE '%" . $safe_search . "%'";
    $search_conditions[] = "actions_log.ACTION_TYPE LIKE '%" . $safe_search . "%'";
    $search_conditions[] = "actions_log.SOURCE LIKE '%" . $safe_search . "%'";
    $search_conditions[] = "actions_log.MODULE LIKE '%" . $safe_search . "%'";
    $search_conditions[] = "actions_log.OBJECT_TITLE LIKE '%" . $safe_search . "%'";
    $search_conditions[] = "actions_log.OBJECT_ID LIKE '%" . $safe_search . "%'";
    $search_conditions[] = "actions_log.URL LIKE '%" . $safe_search . "%'";
    $search_conditions[] = "actions_log.DETAILS_JSON LIKE '%" . $safe_search . "%'";
    $qry .= " AND (" . implode(' OR ', $search_conditions) . ")";
    $out['SEARCH'] = htmlspecialchars($search, ENT_QUOTES);
}

// QUERY READY
global $save_qry;
if ($save_qry) {
    $qry = $session->data['actions_log_qry'];
} else {
    $session->data['actions_log_qry'] = $qry;
}
if (!$qry) {
    $qry = "1";
}

$sortby_actions_log = "actions_log.ID DESC";
$out['SORTBY'] = $sortby_actions_log;

// SEARCH RESULTS
$res_total = SQLSelectOne("SELECT count(*) as TOTAL FROM actions_log WHERE $qry");
$page = gr('page', 'int');
if (!$page) {
    $page = 1;
}
$out['PAGE'] = $page;
$on_page = 50;
$limit = (($page - 1) * $on_page) . ',' . $on_page;

$urlPattern = '?page=(:num)'
    . '&period=' . urlencode($period)
    . '&from=' . urlencode($from)
    . '&to=' . urlencode($to)
    . '&action_type=' . urlencode($action_type)
    . '&source=' . urlencode($source)
    . '&module_name=' . urlencode($module_name)
    . '&result=' . urlencode($result)
    . '&request_method=' . urlencode($request_method)
    . '&user_name=' . urlencode($user_name)
    . '&terminal=' . urlencode($terminal)
    . '&ip=' . urlencode($ip)
    . '&search=' . urlencode($search);

require(ROOT . '3rdparty/Paginator/Paginator.php');
$paginator = new JasonGrimes\Paginator($res_total['TOTAL'], $on_page, $page, $urlPattern);
$out['PAGINATOR'] = $paginator;

$res = SQLSelect("SELECT actions_log.* FROM actions_log WHERE $qry ORDER BY " . $sortby_actions_log . " LIMIT $limit");

if (isset($res[0]['ID'])) {
    $total = count($res);
    for ($i = 0; $i < $total; $i++) {
        $res[$i]['ACTION_TYPE_TITLE'] = $this->getActionLabel($res[$i]['ACTION_TYPE']);
        $res[$i]['SOURCE_TITLE'] = $this->getSourceLabel($res[$i]['SOURCE']);
        $res[$i]['RESULT_TITLE'] = $this->getResultLabel($res[$i]['RESULT']);
        $res[$i]['SOURCE_BADGE_CLASS'] = 'label-default';
        if ($res[$i]['SOURCE'] == 'admin') {
            $res[$i]['SOURCE_BADGE_CLASS'] = 'label-primary';
        } elseif ($res[$i]['SOURCE'] == 'api') {
            $res[$i]['SOURCE_BADGE_CLASS'] = 'label-info';
        } elseif ($res[$i]['SOURCE'] == 'cli') {
            $res[$i]['SOURCE_BADGE_CLASS'] = 'label-warning';
        }

        $res[$i]['RESULT_BADGE_CLASS'] = 'label-default';
        if ($res[$i]['RESULT'] == 'ok') {
            $res[$i]['RESULT_BADGE_CLASS'] = 'label-success';
        } elseif ($res[$i]['RESULT'] == 'error') {
            $res[$i]['RESULT_BADGE_CLASS'] = 'label-danger';
        } elseif ($res[$i]['RESULT'] == 'skipped') {
            $res[$i]['RESULT_BADGE_CLASS'] = 'label-warning';
        }

        $target_parts = array();
        if ($res[$i]['OBJECT_TYPE'] != '') {
            $target_parts[] = $res[$i]['OBJECT_TYPE'];
        }
        if ($res[$i]['OBJECT_TITLE'] != '') {
            $target_parts[] = $res[$i]['OBJECT_TITLE'];
        } elseif ($res[$i]['OBJECT_ID'] != '') {
            $target_parts[] = '#' . $res[$i]['OBJECT_ID'];
        }
        if (isset($target_parts[0])) {
            $res[$i]['TARGET_TITLE'] = implode(': ', $target_parts);
        } else {
            $res[$i]['TARGET_TITLE'] = '-';
        }

        $location_parts = array();
        if ($res[$i]['MODULE'] != '') {
            $location_parts[] = $res[$i]['MODULE'];
        }
        if ($res[$i]['VIEW_MODE'] != '') {
            $location_parts[] = $res[$i]['VIEW_MODE'];
        }
        if ($res[$i]['REQUEST_METHOD'] != '') {
            $location_parts[] = strtoupper($res[$i]['REQUEST_METHOD']);
        }
        if (isset($location_parts[0])) {
            $res[$i]['LOCATION_TITLE'] = implode(' / ', $location_parts);
        } else {
            $res[$i]['LOCATION_TITLE'] = '-';
        }

        $details_rows = array();
        if ($res[$i]['TITLE'] != '') {
            $details_rows[] = $res[$i]['TITLE'];
        }
        if ($res[$i]['URL'] != '') {
            $details_rows[] = 'URL: ' . $res[$i]['URL'];
        }
        if ($res[$i]['REFERER'] != '') {
            $details_rows[] = 'Referer: ' . $res[$i]['REFERER'];
        }
        if ($res[$i]['DETAILS_JSON'] != '') {
            $details_payload = $res[$i]['DETAILS_JSON'];
            $decoded_payload = json_decode($details_payload, true);
            if (is_array($decoded_payload)) {
                $json_options = 0;
                if (defined('JSON_UNESCAPED_UNICODE')) {
                    $json_options = $json_options | JSON_UNESCAPED_UNICODE;
                }
                if (defined('JSON_UNESCAPED_SLASHES')) {
                    $json_options = $json_options | JSON_UNESCAPED_SLASHES;
                }
                if (defined('JSON_PRETTY_PRINT')) {
                    $json_options = $json_options | JSON_PRETTY_PRINT;
                }
                $pretty_payload = json_encode($decoded_payload, $json_options);
                if (is_string($pretty_payload) && $pretty_payload != '') {
                    $details_payload = $pretty_payload;
                }
            }
            $details_rows[] = $details_payload;
        }
        $res[$i]['HAS_DETAILS'] = isset($details_rows[0]) ? 1 : 0;
        $res[$i]['DETAILS_BODY'] = implode("\n\n", $details_rows);
        $res[$i]['ROW_UID'] = 'log_' . $res[$i]['ID'];

        $safe_fields = array(
            'ADDED', 'IP', 'USER', 'TERMINAL', 'ACTION_TYPE_TITLE', 'SOURCE_TITLE', 'RESULT_TITLE',
            'TARGET_TITLE', 'LOCATION_TITLE', 'URL', 'DETAILS_BODY'
        );
        foreach ($safe_fields as $field_name) {
            if (!isset($res[$i][$field_name])) {
                $res[$i][$field_name] = '';
            }
            $res[$i][$field_name . '_HTML'] = htmlspecialchars((string)$res[$i][$field_name], ENT_QUOTES);
        }

        if ($res[$i]['URL'] != '') {
            $short_url = $res[$i]['URL'];
            if (mb_strlen($short_url, 'utf-8') > 80) {
                $short_url = mb_substr($short_url, 0, 80, 'utf-8') . '...';
            }
            $res[$i]['URL_SHORT_HTML'] = htmlspecialchars($short_url, ENT_QUOTES);
        } else {
            $res[$i]['URL_SHORT_HTML'] = '';
        }
    }
    $out['RESULT'] = $res;
}

$action_types = SQLSelect("SELECT DISTINCT(ACTION_TYPE) as VALUE FROM actions_log WHERE ACTION_TYPE!='' ORDER BY ACTION_TYPE");
for ($i = 0; $i < count($action_types); $i++) {
    $action_types[$i]['TITLE'] = $this->getActionLabel($action_types[$i]['VALUE']);
    if ($action_types[$i]['VALUE'] == $action_type) {
        $action_types[$i]['SELECTED'] = 1;
    }
}
$out['ACTION_TYPES'] = $action_types;

$sources = SQLSelect("SELECT DISTINCT(SOURCE) as VALUE FROM actions_log WHERE SOURCE!='' ORDER BY SOURCE");
for ($i = 0; $i < count($sources); $i++) {
    $sources[$i]['TITLE'] = $this->getSourceLabel($sources[$i]['VALUE']);
    if ($sources[$i]['VALUE'] == $source) {
        $sources[$i]['SELECTED'] = 1;
    }
}
$out['SOURCES'] = $sources;

$modules = SQLSelect("SELECT DISTINCT(MODULE) as VALUE FROM actions_log WHERE MODULE!='' ORDER BY MODULE");
for ($i = 0; $i < count($modules); $i++) {
    if ($modules[$i]['VALUE'] == $module_name) {
        $modules[$i]['SELECTED'] = 1;
    }
}
$out['MODULES_LIST'] = $modules;

$results = SQLSelect("SELECT DISTINCT(RESULT) as VALUE FROM actions_log WHERE RESULT!='' ORDER BY RESULT");
for ($i = 0; $i < count($results); $i++) {
    $results[$i]['TITLE'] = $this->getResultLabel($results[$i]['VALUE']);
    if ($results[$i]['VALUE'] == $result) {
        $results[$i]['SELECTED'] = 1;
    }
}
$out['RESULTS_LIST'] = $results;

$methods = SQLSelect("SELECT DISTINCT(REQUEST_METHOD) as VALUE FROM actions_log WHERE REQUEST_METHOD!='' ORDER BY REQUEST_METHOD");
for ($i = 0; $i < count($methods); $i++) {
    if ($methods[$i]['VALUE'] == $request_method) {
        $methods[$i]['SELECTED'] = 1;
    }
}
$out['METHODS_LIST'] = $methods;
