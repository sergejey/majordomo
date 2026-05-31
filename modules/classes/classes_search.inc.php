<?php
/*
* @version 0.1 (auto-set)
*/

$go_linked_object = gr('go_linked_object');
if (strtolower($go_linked_object) == 'allscripts') {
    $this->redirect(ROOTHTML."panel/scripts.html");
}

if ($go_linked_object != '') {
    $tmp = SQLSelectOne("SELECT ID, CLASS_ID FROM objects WHERE TITLE='" . DBSafe($go_linked_object) . "'");
    if ($tmp['ID']) {
        $this->redirect(ROOTHTML.'panel/class/'.$tmp['CLASS_ID'].'/object/'.$tmp['ID'].'.html?tab=properties');
    }
}


global $session;
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$qry = "1";
// search filters
// QUERY READY
global $save_qry;
if ($save_qry) {
    $qry = $session->data['classes_qry'];
} else {
    $session->data['classes_qry'] = $qry;
}
if (!$qry) $qry = "1";
// FIELDS ORDER
global $sortby;
if (!$sortby) {
    $sortby = isset($session->data['classes_sort']) ? $session->data['classes_sort'] : '';
} else {
    if ($session->data['classes_sort'] == $sortby) {
        if (Is_Integer(strpos($sortby, ' DESC'))) {
            $sortby = str_replace(' DESC', '', $sortby);
        } else {
            $sortby = $sortby . " DESC";
        }
    }
    $session->data['classes_sort'] = $sortby;
}
if (!$sortby) $sortby = "TITLE";
$out['SORTBY'] = $sortby;
// SEARCH RESULTS

startMeasure('getClasses');
$res = SQLSelect("SELECT * FROM classes WHERE $qry ORDER BY $sortby");
if ($res[0]['ID']) {
    //colorizeArray($res);
    $total = count($res);

    $class_methods = array();

    for ($i = 0; $i < $total; $i++) {
        // some action for every record if required
        $objects = SQLSelect("SELECT ID, TITLE, CLASS_ID, DESCRIPTION FROM objects WHERE CLASS_ID='" . $res[$i]['ID'] . "' ORDER BY objects.TITLE");
        if (isset($objects[0]['ID'])) {
            $total_o = count($objects);
            for ($o = 0; $o < $total_o; $o++) {
                $objects[$o]['KEY_DATA'] = getKeyData($objects[$o]['ID']);
                $methods = SQLSelect("SELECT ID, TITLE FROM methods WHERE OBJECT_ID='" . $objects[$o]['ID'] . "' ORDER BY methods.TITLE");
                if (isset($class_methods[$objects[$o]['CLASS_ID']])) {
                    $parent_methods = $class_methods[$objects[$o]['CLASS_ID']];
                } else {
                    startMeasure('getParentMethods');
                    $parent_methods = $this->getParentMethods($objects[$o]['CLASS_ID'], '', 1);
                    endMeasure('getParentMethods');
                    $class_methods[$objects[$o]['CLASS_ID']] = $parent_methods;
                }

                $parent_methods_titles = array();
                if (isset($parent_methods[0]['ID'])) {
                    foreach ($parent_methods as $k => $v) {
                        $parent_methods_titles[$v['TITLE']] = $v['ID'];
                    }
                }
                if (isset($methods[0]['ID'])) {
                    $total_m = count($methods);
                    for ($im = 0; $im < $total_m; $im++) {
                        if ($parent_methods_titles[$methods[$im]['TITLE']]) {
                            $methods[$im]['ID'] = $parent_methods_titles[$methods[$im]['TITLE']];
                        }
                    }
                    $objects[$o]['METHODS'] = $methods;
                }
            }
            $res[$i]['OBJECTS'] = $objects;
            if (!is_array($res[$i]['OBJECTS'])) {
                unset($res[$i]['OBJECTS']);
            }
        }
    }
    endMeasure('getClasses');
    startMeasure('classesBuildTree');
    $res = $this->buildTree_classes($res);
    endMeasure('classesBuildTree');
    $out['RESULT'] = $res;
}
