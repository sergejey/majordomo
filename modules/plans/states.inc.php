<?php

$plan_id=$rec['ID'];

$state_id=gr('state_id');
if ($state_id) {
    $state=SQLSelectOne("SELECT * FROM plan_states WHERE PLAN_ID=".(int)$plan_id." AND ID=".(int)$state_id);
    if (gr('duplicate') && $state['ID']) {
        unset($state['ID']);
        $state['TITLE'].=' (copy)';
        $state['ID']=SQLInsert('plan_states',$state);
        $this->redirect("?view_mode=".$this->view_mode."&id=".$plan_id."&tab=".$this->tab."&state_id=".$state['ID']);
    }
    if (gr('delete') && $state['ID']) {
        SQLExec("DELETE FROM plan_states WHERE ID=".$state['ID']);
        $this->redirect("?view_mode=".$this->view_mode."&id=".$plan_id."&tab=".$this->tab);
    }
    if ($this->mode=='update') {
        $ok=1;
        $state['PLAN_ID']=$plan_id;
        $state['TITLE']=gr('state_title');
        if (!$state['TITLE']) {
            $ok=0;
            $out['ERR_TITLE']=1;
        }

        $state['ITEM']=gr('state_item','trim');
        if (!$state['ITEM']) {
            $ok=0;
            $out['ERR_ITEM']=1;
        }
        $state['CSS_CLASS']=gr('state_css_class','trim');
        $state['CSS_CLASS_INVERSE']=gr('state_css_class_inverse','trim');

        $state['LINKED_OBJECT']=gr('state_linked_object','trim');
        $state['LINKED_PROPERTY']=gr('state_linked_property','trim');
        $state['CONDITION']=gr('state_condition','trim');
        $state['CONDITION_VALUE']=gr('state_condition_value','trim');

        $do_on_click=gr('do_on_click');
        if ($do_on_click=='run_code') {
            $state['CODE']=gr('code');
        } else {
            $state['CODE']='';
        }
        if ($do_on_click=='run_script') {
            $state['SCRIPT_ID']=gr('script_id');
        } else {
            $state['SCRIPT_ID']=0;
        }
        if ($do_on_click=='open_menu') {
            $state['MENU_ITEM_ID']=gr('menu_item_id');
        } else {
            $state['MENU_ITEM_ID']=0;
        }
        if ($do_on_click=='run_method') {
            $state['ACTION_OBJECT']=gr('action_object');
            $state['ACTION_METHOD']=gr('action_method');
        } else {
            $state['ACTION_OBJECT']='';
            $state['ACTION_METHOD']='';
        }
        if ($do_on_click=='show_homepage') {
            $state['HOMEPAGE_ID']=gr('homepage_id','int');
        } else {
            $state['HOMEPAGE_ID']=0;
        }
        if ($do_on_click=='show_url') {
            $state['EXT_URL']=gr('ext_url');
        } else {
            $state['EXT_URL']='';
        }

        if ($ok) {
            if ($state['ID']) {
                SQLUpdate('plan_states',$state);
            } else {
                $state['ID']=SQLInsert('plan_states',$state);
            }
            $out['OK']=1;
            $this->redirect("?view_mode=".$this->view_mode."&id=".$plan_id."&tab=".$this->tab);
        } else {
            $out['ERR']=1;
        }
    }
    if (is_array($state)) {
        foreach($state as $k=>$v) {
            $out['STATE_'.$k]=$v;
        }
        $also_qry="0";
        if ($state['LINKED_OBJECT']) {
            $also_qry.=" OR plan_states.LINKED_OBJECT='".DBSafe($state['LINKED_OBJECT'])."'";
        }
        if ($state['ITEM']) {
            $also_qry.=" OR plan_states.ITEM LIKE '".DBSafe($state['ITEM'])."'";
        }
        $other_items=SQLSelect("SELECT * FROM plan_states WHERE PLAN_ID=".(int)$state['PLAN_ID']." AND (".$also_qry.") ORDER BY TITLE");
        if ($other_items[0]['ID']) {
            $out['OTHER_ITEMS']=$other_items;
        }
    }

    $out['STATE_ID']=$state_id;
}

$qry="1";
$search=gr('search','trim');
if ($search) {
    $qry.=" AND (plan_states.TITLE LIKE '%".DBSafe($search)."%' OR";
    $qry.=" plan_states.LINKED_OBJECT LIKE '%".DBSafe($search)."%' OR";
    $qry.=" plan_states.LINKED_PROPERTY LIKE '%".DBSafe($search)."%' OR";
    $qry.=" plan_states.ITEM LIKE '%".DBSafe($search)."%' OR";
    $qry.=" plan_states.CSS_CLASS LIKE '%".DBSafe($search)."%' OR";
    $qry.=" plan_states.CSS_CLASS_INVERSE LIKE '%".DBSafe($search)."%' OR";
    $qry.=" 0)";
    $out['SEARCH']=$search;
}

$states=SQLSelect("SELECT * FROM plan_states WHERE PLAN_ID=".(int)$plan_id." AND $qry ORDER BY TITLE");
$out['STATES']=$states;

$out['CLASSES']=$this->getCSSClasses($plan_id);
$out['ITEMS']=$this->getImageItems($plan_id);

$out['HOMEPAGES']=SQLSelect("SELECT ID, TITLE FROM layouts ORDER BY TITLE");
$out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
$menu_items=SQLSelect("SELECT ID, TITLE, PARENT_ID FROM commands WHERE EXT_ID=0 ORDER BY PARENT_ID, TITLE");
$titles=array();
foreach($menu_items as $k=>$v) {
    $titles[$v['ID']]=$v['TITLE'];
}
$total=count($menu_items);
for($i=0;$i<$total;$i++) {
    if ($titles[$menu_items[$i]['PARENT_ID']]) {
        $menu_items[$i]['TITLE']=$titles[$menu_items[$i]['PARENT_ID']].' &gt; '.$menu_items[$i]['TITLE'];
    }
}
$out['MENU_ITEMS']=$menu_items;