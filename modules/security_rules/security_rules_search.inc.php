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
  if (IsSet($this->object_id)) {
   $object_id=$this->object_id;
   $qry.=" AND OBJECT_ID='".$this->object_id."'";
  } else {
   global $object_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['security_rules_qry'];
  } else {
   $session->data['security_rules_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_security_rules;
  if (!$sortby_security_rules) {
   $sortby_security_rules=$session->data['security_rules_sort'];
  } else {
   if ($session->data['security_rules_sort']==$sortby_security_rules) {
    if (Is_Integer(strpos($sortby_security_rules, ' DESC'))) {
     $sortby_security_rules=str_replace(' DESC', '', $sortby_security_rules);
    } else {
     $sortby_security_rules=$sortby_security_rules." DESC";
    }
   }
   $session->data['security_rules_sort']=$sortby_security_rules;
  }
  if (!$sortby_security_rules) $sortby_security_rules="ID DESC";
  $out['SORTBY']=$sortby_security_rules;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM security_rules WHERE $qry ORDER BY ".$sortby_security_rules);
  if ($this->single_rec) {
   $total_res=count($res);
   if (!$total_res && $this->action=='admin') {
    $this->redirect("?view_mode=edit_security_rules");
   } elseif ($total_res && $this->action=='admin') {
    $this->redirect("?view_mode=edit_security_rules&id=".$res[0]['ID']);
   } elseif ($total_res && $this->action!='admin') {
   }
  }
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   $total=count($res);
   $sr_users_map = array('0' => 'Anonymous');
   $sr_users_rows = SQLSelect("SELECT ID, NAME FROM users");
   if (is_array($sr_users_rows)) {
       foreach ($sr_users_rows as $sr_u) {
           if (isset($sr_u['ID'])) {
               $sr_users_map[(string)$sr_u['ID']] = isset($sr_u['NAME']) ? $sr_u['NAME'] : '';
           }
       }
   }
   $sr_terminals_map = array('0' => 'Unknown');
   $sr_terminals_rows = getAllTerminals(-1, 'TITLE');
   if (is_array($sr_terminals_rows)) {
       foreach ($sr_terminals_rows as $sr_t) {
           if (is_array($sr_t) && isset($sr_t['ID'])) {
               $sr_terminals_map[(string)$sr_t['ID']] = isset($sr_t['TITLE']) ? $sr_t['TITLE'] : '';
           }
       }
   }
   $sr_cond_ops = array('1' => '=', '2' => '&gt;', '3' => '&lt;', '4' => '&lt;&gt;', '5' => '~');
   $sr_modules_set = array();
   $sr_mrows = SQLSelect("SELECT NAME FROM project_modules");
   if (is_array($sr_mrows)) { foreach ($sr_mrows as $sr_mr) { if (isset($sr_mr['NAME'])) $sr_modules_set[$sr_mr['NAME']] = 1; } }
   $sr_type_map = array(
       'terminal' => array('terminals',        'edit_terminals', 'terminals'),
       'scene'    => array('scenes',           'edit_scenes',    'scenes'),
       'layout'   => array('layouts',          'edit_layouts',   'layouts'),
       'pattern'  => array('patterns',         'edit_patterns',  'patterns'),
       'menu'     => array('commands',         'edit_commands',  'commands'),
       'media'    => array('app_mediabrowser', 'edit',           'collections'),
   );
   $sr_ent_cache = array();
   $sr_tbl_ok = array();
   $sr_pd_cache = array();
   $sr_build_pd = function ($sr_m) use (&$sr_pd_cache) {
       if (!isset($sr_pd_cache[$sr_m])) {
           $sr_pd_cache[$sr_m] = $this->codeParams($sr_m . ':{instance=adm}') . $this->codeParams('panel:{action=' . $sr_m . '}');
       }
       return $sr_pd_cache[$sr_m];
   };
   $sr_obj_id_cache = array();
   $sr_resolve_obj_id = function ($sr_name) use (&$sr_obj_id_cache) {
       $sr_name = trim($sr_name);
       if ($sr_name === '') return 0;
       if (isset($sr_obj_id_cache[$sr_name])) return $sr_obj_id_cache[$sr_name];
       $sr_oid = 0;
       if (preg_match('/^(.+?)\.(.+?)$/', $sr_name, $sr_m)) {
           $sr_r = SQLSelectOne("SELECT objects.ID FROM objects LEFT JOIN classes ON objects.CLASS_ID=classes.ID WHERE objects.TITLE='" . DBSafe($sr_m[2]) . "' AND classes.TITLE='" . DBSafe($sr_m[1]) . "'");
           if (isset($sr_r['ID'])) $sr_oid = (int)$sr_r['ID'];
       }
       if (!$sr_oid) {
           $sr_r = SQLSelectOne("SELECT ID FROM objects WHERE TITLE='" . DBSafe($sr_name) . "'");
           if (isset($sr_r['ID'])) $sr_oid = (int)$sr_r['ID'];
       }
       $sr_obj_id_cache[$sr_name] = $sr_oid;
       return $sr_oid;
   };
   for($i=0;$i<$total;$i++) {
    $res[$i]['OBJECT_ORPHAN'] = 0;
    $sr_ot0 = isset($res[$i]['OBJECT_TYPE']) ? $res[$i]['OBJECT_TYPE'] : '';
    $sr_oi0 = isset($res[$i]['OBJECT_ID']) ? (int)$res[$i]['OBJECT_ID'] : 0;
    if (isset($sr_type_map[$sr_ot0]) && $sr_type_map[$sr_ot0][2] !== '' && $sr_oi0 > 0) {
        $sr_tb0 = $sr_type_map[$sr_ot0][2];
        if (!isset($sr_tbl_ok[$sr_tb0])) {
            $sr_tchk = SQLSelectOne("SELECT 1 AS x FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='" . $sr_tb0 . "' LIMIT 1");
            $sr_tbl_ok[$sr_tb0] = isset($sr_tchk['x']);
        }
        if ($sr_tbl_ok[$sr_tb0]) {
            $sr_cnt = SQLSelectOne("SELECT COUNT(*) AS c FROM " . $sr_tb0 . " WHERE ID='" . $sr_oi0 . "'");
            if (isset($sr_cnt['c']) && (int)$sr_cnt['c'] === 0) {
                $res[$i]['OBJECT_ORPHAN'] = 1;
            }
        }
    }
    if (isset($res[$i]['TIMES']) && $res[$i]['TIMES'] !== '') {
        $sr_hours = array();
        foreach (explode(',', $res[$i]['TIMES']) as $sr_rng) {
            $sr_rng = trim($sr_rng);
            if ($sr_rng !== '' && preg_match('/^(\\d{1,2}):00-/', $sr_rng, $sr_hm)) { $sr_hours[] = (int)$sr_hm[1]; }
        }
        if (count($sr_hours) > 0) {
            sort($sr_hours);
            $sr_hours = array_values(array_unique($sr_hours));
            $sr_spans = array();
            $sr_a = $sr_hours[0]; $sr_b = $sr_hours[0];
            $sr_n = count($sr_hours);
            for ($sr_k = 1; $sr_k < $sr_n; $sr_k++) {
                if ($sr_hours[$sr_k] == $sr_b + 1) { $sr_b = $sr_hours[$sr_k]; }
                else { $sr_spans[] = sprintf('%02d:00-%02d:00', $sr_a, $sr_b + 1); $sr_a = $sr_b = $sr_hours[$sr_k]; }
            }
            $sr_spans[] = sprintf('%02d:00-%02d:00', $sr_a, $sr_b + 1);
            $res[$i]['TIMES'] = implode(', ', $sr_spans);
        } else {
            $res[$i]['TIMES'] = str_replace(',', ', ', $res[$i]['TIMES']);
        }
        if (!empty($res[$i]['TIMES_EXCEPT'])) {
            $res[$i]['TIMES'] = '<span style="color:#b00;text-decoration:line-through">' . $res[$i]['TIMES'] . '</span>';
        }
    }
    $sr_u_style = !empty($res[$i]['USERS_EXCEPT']) ? ' style="color:#b00;text-decoration:line-through"' : '';
    $res[$i]['USERS_TITLES'] = '';
    if (isset($res[$i]['USERS']) && $res[$i]['USERS'] !== '') {
        $sr_names = array();
        foreach (explode(',', $res[$i]['USERS']) as $sr_id) {
            $sr_id = trim($sr_id);
            if ($sr_id === '') continue;
            $sr_nm = isset($sr_users_map[$sr_id]) ? $sr_users_map[$sr_id] : ('#' . $sr_id);
            $sr_disp = htmlspecialchars($sr_nm, ENT_QUOTES);
            if ((int)$sr_id > 0 && isset($sr_users_map[$sr_id])) {
                $sr_disp = '<a' . $sr_u_style . ' href="' . ROOTHTML . 'admin.php?pd=' . $sr_build_pd('users') . '&md=users&inst=adm&view_mode=edit_users&id=' . (int)$sr_id . '">' . $sr_disp . '</a>';
            } elseif (!isset($sr_users_map[$sr_id])) {
                $sr_disp = '<span style="color:#999;text-decoration:line-through">' . $sr_disp . '</span>';
            } elseif ($sr_u_style !== '') {
                $sr_disp = '<span' . $sr_u_style . '>' . $sr_disp . '</span>';
            }
            $sr_names[] = $sr_disp;
        }
        $res[$i]['USERS_TITLES'] = implode(', ', $sr_names);
    }
    $sr_t_style = !empty($res[$i]['TERMINALS_EXCEPT']) ? ' style="color:#b00;text-decoration:line-through"' : '';
    $res[$i]['TERMINALS_TITLES'] = '';
    if (isset($res[$i]['TERMINALS']) && $res[$i]['TERMINALS'] !== '') {
        $sr_names = array();
        foreach (explode(',', $res[$i]['TERMINALS']) as $sr_id) {
            $sr_id = trim($sr_id);
            if ($sr_id === '') continue;
            $sr_nm = isset($sr_terminals_map[$sr_id]) ? $sr_terminals_map[$sr_id] : ('#' . $sr_id);
            $sr_disp = htmlspecialchars($sr_nm, ENT_QUOTES);
            if ((int)$sr_id > 0 && isset($sr_terminals_map[$sr_id])) {
                $sr_disp = '<a' . $sr_t_style . ' href="' . ROOTHTML . 'admin.php?pd=' . $sr_build_pd('terminals') . '&md=terminals&inst=adm&view_mode=edit_terminals&id=' . (int)$sr_id . '">' . $sr_disp . '</a>';
            } elseif (!isset($sr_terminals_map[$sr_id])) {
                $sr_disp = '<span style="color:#999;text-decoration:line-through">' . $sr_disp . '</span>';
            } elseif ($sr_t_style !== '') {
                $sr_disp = '<span' . $sr_t_style . '>' . $sr_disp . '</span>';
            }
            $sr_names[] = $sr_disp;
        }
        $res[$i]['TERMINALS_TITLES'] = implode(', ', $sr_names);
    }
    $sr_cond_parts = array();
    if (!empty($res[$i]['CONDITION_ACTIVE'])) {
        if (isset($res[$i]['CONDITION_LINKED_OBJECT']) && $res[$i]['CONDITION_LINKED_OBJECT'] !== ''
            && isset($res[$i]['CONDITION_LINKED_PROPERTY']) && $res[$i]['CONDITION_LINKED_PROPERTY'] !== '') {
            $sr_op = isset($sr_cond_ops[(string)$res[$i]['CONDITION']]) ? $sr_cond_ops[(string)$res[$i]['CONDITION']] : '?';
            $sr_oid = $sr_resolve_obj_id($res[$i]['CONDITION_LINKED_OBJECT']);
            $sr_obj_disp = htmlspecialchars($res[$i]['CONDITION_LINKED_OBJECT'], ENT_QUOTES);
            if ($sr_oid) { $sr_obj_disp = '<a href="' . ROOTHTML . 'admin.php?pd=' . $sr_build_pd('objects') . '&md=objects&inst=adm&view_mode=edit_objects&id=' . $sr_oid . '">' . $sr_obj_disp . '</a>'; }
            else { $sr_obj_disp = '<span style="color:#999;text-decoration:line-through">' . $sr_obj_disp . '</span>'; }
            $sr_cond_parts[] = $sr_obj_disp . '.' . htmlspecialchars($res[$i]['CONDITION_LINKED_PROPERTY'], ENT_QUOTES)
                . ' ' . $sr_op . ' ' . htmlspecialchars((string)(isset($res[$i]['CONDITION_VALUE']) ? $res[$i]['CONDITION_VALUE'] : ''), ENT_QUOTES);
        }
        if (isset($res[$i]['CONDITIONS']) && $res[$i]['CONDITIONS'] !== '') {
            $sr_conds = json_decode($res[$i]['CONDITIONS'], true);
            if (is_array($sr_conds)) {
                foreach ($sr_conds as $sr_c) {
                    if (!is_array($sr_c)) continue;
                    $sr_lo = isset($sr_c['LINKED_OBJECT']) ? $sr_c['LINKED_OBJECT'] : '';
                    $sr_lp = isset($sr_c['LINKED_PROPERTY']) ? $sr_c['LINKED_PROPERTY'] : '';
                    if ($sr_lo === '' || $sr_lp === '') continue;
                    $sr_op = isset($sr_cond_ops[(string)$sr_c['CONDITION']]) ? $sr_cond_ops[(string)$sr_c['CONDITION']] : '?';
                    $sr_val = isset($sr_c['VALUE']) ? $sr_c['VALUE'] : '';
                    $sr_oid = $sr_resolve_obj_id($sr_lo);
                    $sr_obj_disp = htmlspecialchars($sr_lo, ENT_QUOTES);
                    if ($sr_oid) { $sr_obj_disp = '<a href="' . ROOTHTML . 'admin.php?pd=' . $sr_build_pd('objects') . '&md=objects&inst=adm&view_mode=edit_objects&id=' . $sr_oid . '">' . $sr_obj_disp . '</a>'; }
                    else { $sr_obj_disp = '<span style="color:#999;text-decoration:line-through">' . $sr_obj_disp . '</span>'; }
                    $sr_cond_parts[] = $sr_obj_disp . '.' . htmlspecialchars($sr_lp, ENT_QUOTES) . ' ' . $sr_op . ' ' . htmlspecialchars((string)$sr_val, ENT_QUOTES);
                }
            }
        }
    }
    $res[$i]['CONDITIONS_SUMMARY'] = implode(' &amp;&amp; ', $sr_cond_parts);
    $sr_otype = isset($res[$i]['OBJECT_TYPE']) ? $res[$i]['OBJECT_TYPE'] : '';
    $sr_oid2  = isset($res[$i]['OBJECT_ID']) ? (int)$res[$i]['OBJECT_ID'] : 0;
    $sr_otype_disp = htmlspecialchars($sr_otype, ENT_QUOTES);
    $sr_obj_url = ''; $sr_ttable = '';
    if (isset($sr_type_map[$sr_otype])) {
        list($sr_tmod, $sr_tvm, $sr_ttable) = $sr_type_map[$sr_otype];
        if (isset($sr_modules_set[$sr_tmod])) {
            $sr_obj_url = ROOTHTML . 'admin.php?pd=' . $sr_build_pd($sr_tmod) . '&md=' . $sr_tmod . '&inst=adm&view_mode=' . $sr_tvm . '&id=' . $sr_oid2;
        }
    } elseif (isset($sr_modules_set[$sr_otype])) {
        $sr_obj_url = ROOTHTML . 'admin.php?pd=' . $sr_build_pd($sr_otype) . '&md=' . $sr_otype . '&inst=adm';
        if (!isset($sr_ent_cache['tbl:' . $sr_otype])) { // SR_TYPE_TABLE
            $sr_ent_cache['tbl:' . $sr_otype] = (preg_match('/^[a-z0-9_]+$/i', $sr_otype) && SQLSelectOne("SHOW TABLES LIKE '" . $sr_otype . "'")) ? 1 : 0;
        }
        if ($sr_ent_cache['tbl:' . $sr_otype]) {
            $sr_ttable = $sr_otype;
            $sr_obj_url .= '&view_mode=edit_' . $sr_otype . '&id=' . $sr_oid2;
        }
    }
    $sr_title = ''; $sr_name = '';
    if ($sr_ttable !== '' && $sr_oid2 > 0) {
        $sr_ck = $sr_ttable . ':' . $sr_oid2;
        if (!isset($sr_ent_cache[$sr_ck])) {
            $sr_ent_cache[$sr_ck] = SQLSelectOne("SELECT * FROM " . $sr_ttable . " WHERE ID='" . $sr_oid2 . "'");
        }
        $sr_ent = $sr_ent_cache[$sr_ck];
        if (is_array($sr_ent)) {
            if (isset($sr_ent['TITLE'])) $sr_title = (string)$sr_ent['TITLE'];
            if (isset($sr_ent['NAME']))  $sr_name  = (string)$sr_ent['NAME'];
        }
    }
    $sr_label = ($sr_title !== '') ? $sr_title : (($sr_name !== '') ? $sr_name : $sr_otype);
    $sr_label_esc = htmlspecialchars($sr_label, ENT_QUOTES);
    $sr_main = ($sr_obj_url !== '') ? ('<a href="' . $sr_obj_url . '">' . $sr_label_esc . '</a>') : $sr_label_esc;
    $sr_parts = array($sr_main);
    if ($sr_name !== '' && $sr_title !== '') $sr_parts[] = htmlspecialchars($sr_name, ENT_QUOTES);
    if ($sr_title !== '' || $sr_name !== '') {
        $sr_parts[] = $sr_otype_disp . ' #' . $sr_oid2;
    } else {
        $sr_parts[0] = $sr_main . ' #' . $sr_oid2;
    }
    if (!empty($res[$i]['OBJECT_ORPHAN'])) {
        $res[$i]['OBJECT_LINKED'] = '<span style="color:#999;text-decoration:line-through">' . $sr_otype_disp . ' #' . $sr_oid2 . '</span>';
    } else {
        $res[$i]['OBJECT_LINKED'] = implode(' &middot; ', $sr_parts);
    }
   }
   $out['RESULT']=$res;
  }
?>