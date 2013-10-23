<?php
/**
* Products 
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.3 (wizard, 21:04:03 [Apr 16, 2011])
*/
//
//
class app_products extends module {
/**
* app_products
*
* Module class constructor
*
* @access private
*/
function app_products() {
  $this->name="app_products";
  $this->title="<#LANG_APP_PRODUCTS#>";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  if (IsSet($this->category_id)) {
   $out['IS_SET_CATEGORY_ID']=1;
  }
  if (IsSet($this->product_id)) {
   $out['IS_SET_PRODUCT_ID']=1;
  }
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='products' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_products') {
   $this->search_products($out);
  }
  if ($this->view_mode=='edit_products') {
   $this->edit_products($out, $this->id);
  }
  if ($this->view_mode=='delete_products') {
   $this->delete_products($this->id);
   $this->redirect("?data_source=products");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='product_categories') {
  if ($this->view_mode=='' || $this->view_mode=='search_product_categories') {
   $this->search_product_categories($out);
  }
  if ($this->view_mode=='edit_product_categories') {
   $this->edit_product_categories($out, $this->id);
  }
  if ($this->view_mode=='delete_product_categories') {
   $this->delete_product_categories($this->id);
   $this->redirect("?data_source=product_categories");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='shopping_list_items') {
  if ($this->view_mode=='' || $this->view_mode=='search_shopping_list_items') {
   $this->search_shopping_list_items($out);
  }
  if ($this->view_mode=='delete_shopping_list_items') {
   $this->delete_shopping_list_items($this->id);
   $this->redirect("?data_source=shopping_list_items");
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* products search
*
* @access public
*/
 function search_products(&$out) {
  require(DIR_MODULES.$this->name.'/products_search.inc.php');
 }
/**
* products edit/add
*
* @access public
*/
 function edit_products(&$out, $id) {
  require(DIR_MODULES.$this->name.'/products_edit.inc.php');
 }
/**
* products delete record
*
* @access public
*/
 function delete_products($id) {
  $rec=SQLSelectOne("SELECT * FROM products WHERE ID='$id'");
  // some action for related tables
  @unlink(ROOT.'./cms/products/'.$rec['IMAGE']);
  SQLExec("DELETE FROM product_codes WHERE PRODUCT_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM product_log WHERE PRODUCT_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM shopping_list_items WHERE PRODUCT_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM products WHERE ID='".$rec['ID']."'");
  
 }

 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function addToList($id) {
   $product=SQLSelectOne("SELECT * FROM products WHERE ID='".(int)$id."'");
   if ($product['ID']) {
    SQLExec("DELETE FROM shopping_list_items WHERE PRODUCT_ID='".(int)$id."'");
    $rec=array();
    $rec['PRODUCT_ID']=$product['ID'];
    $rec['TITLE']=$product['TITLE'];
    $rec['IN_CART']=0;
    SQLInsert('shopping_list_items', $rec);
    if (defined('DROPBOX_SHOPPING_LIST')) {
     $data=LoadFile(DROPBOX_SHOPPING_LIST);
     $data=str_replace("\r", '', $data);
     $lines=explode("\n", $data);
     $total=count($lines);
     $found=0;
     for($i=0;$i<$total;$i++) {
      if ($found) {
       continue;
      }
      if (is_integer(strpos($lines[$i], $product['TITLE']))) {
       $found=1;
      }
     }
     if (!$found) {
      if (!$data) {
       $lines=array();
       $lines[]='SHOPPING LIST';
       $lines[]='';
      }
      $lines[]=$product['TITLE'];
      $data=implode("\n", $lines);
      SaveFile(DROPBOX_SHOPPING_LIST, $data);
     }
    }
   }
  }

/**
* Title
*
* Description
*
* @access public
*/
 function removeFromList($id) {
   $product=SQLSelectOne("SELECT * FROM products WHERE ID='".(int)$id."'");
   if ($product['ID']) {
    SQLExec("DELETE FROM shopping_list_items WHERE PRODUCT_ID='".(int)$id."'");
    if (defined('DROPBOX_SHOPPING_LIST')) {
     $data=LoadFile(DROPBOX_SHOPPING_LIST);
     $data=str_replace("\r", '', $data);
     $lines=explode("\n", $data);
     $total=count($lines);
     $found=0;
     $res_lines=array();
     for($i=0;$i<$total;$i++) {
      if (is_integer(strpos($lines[$i], $product['TITLE']))) {
       $found=1;
      } else {
       $res_lines[]=$lines[$i];
      }
     }
     if ($found) {
      $data=implode("\n", $res_lines);
      SaveFile(DROPBOX_SHOPPING_LIST, $data);
     }
    }
   }
 }

/**
* product_categories search
*
* @access public
*/
 function search_product_categories(&$out) {
  require(DIR_MODULES.$this->name.'/product_categories_search.inc.php');
 }
/**
* product_categories edit/add
*
* @access public
*/
 function edit_product_categories(&$out, $id) {
  require(DIR_MODULES.$this->name.'/product_categories_edit.inc.php');
 }
/**
* product_categories delete record
*
* @access public
*/
 function delete_product_categories($id) {
  $rec=SQLSelectOne("SELECT * FROM product_categories WHERE ID='$id'");
  // some action for related tables
  if ($rec['SUB_LIST']!='' && $rec['SUB_LIST']!=$rec['ID']) {
   return;
  }
  SQLExec("DELETE FROM product_categories WHERE ID='".$rec['ID']."'");
 }
/**
* product_categories build tree
*
* @access private
*/
 function buildTree_product_categories($res, $parent_id=0, $level=0) {
  $total=count($res);
  $res2=array();
  for($i=0;$i<$total;$i++) {
   if ($res[$i]['PARENT_ID']==$parent_id) {
    $res[$i]['LEVEL']=$level;
    $res[$i]['RESULT']=$this->buildTree_product_categories($res, $res[$i]['ID'], ($level+1));
    $res[$i]['CATS']=&$res[$i]['RESULT'];
    $res2[]=$res[$i];
   }
  }
  $total2=count($res2);
  if ($total2) {
   return $res2;
  }
 }
/**
* product_categories update tree
*
* @access private
*/
 function updateTree_product_categories($parent_id=0, $parent_list='') {
  $table='product_categories';
  if (!is_array($parent_list)) {
   $parent_list=array();
  }
  $sub_list=array();
  $res=SQLSelect("SELECT * FROM $table WHERE PARENT_ID='$parent_id'");
  $total=count($res);
  for($i=0;$i<$total;$i++) {
   if ($parent_list[0]) {
    $res[$i]['PARENT_LIST']=implode(',', $parent_list);
   } else {
    $res[$i]['PARENT_LIST']='0';
   }
   $sub_list[]=$res[$i]['ID'];
   $tmp_parent=$parent_list;
   $tmp_parent[]=$res[$i]['ID'];
   $sub_this=$this->updateTree_product_categories($res[$i]['ID'], $tmp_parent);
   if ($sub_this[0]) {
    $res[$i]['SUB_LIST']=implode(',', $sub_this);
   } else {
    $res[$i]['SUB_LIST']=$res[$i]['ID'];
   }
   SQLUpdate($table, $res[$i]);
   $sub_list=array_merge($sub_list, $sub_this);
  }
  return $sub_list;
 }
/**
* shopping_list_items search
*
* @access public
*/
 function search_shopping_list_items(&$out) {
  require(DIR_MODULES.$this->name.'/shopping_list_items_search.inc.php');
 }
/**
* shopping_list_items delete record
*
* @access public
*/
 function delete_shopping_list_items($id) {
  $rec=SQLSelectOne("SELECT * FROM shopping_list_items WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM shopping_list_items WHERE ID='".$rec['ID']."'");
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
 @umask(0);
  if (!Is_Dir(ROOT."./cms/products/")) {
   mkdir(ROOT."./cms/products/", 0777);
  }
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS products');
  SQLExec('DROP TABLE IF EXISTS product_categories');
  SQLExec('DROP TABLE IF EXISTS shopping_list_items');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
products - Products
product_categories - Categories
shopping_list_items - Shopping List
*/
  $data = <<<EOD

 products: ID int(10) unsigned NOT NULL auto_increment
 products: TITLE varchar(255) NOT NULL DEFAULT ''
 products: CATEGORY_ID int(10) NOT NULL DEFAULT '0'
 products: IMAGE varchar(70) NOT NULL DEFAULT ''
 products: WILL_EXPIRE int(3) NOT NULL DEFAULT '0'
 products: EXPIRE_DATE date
 products: EXPIRE_DEFAULT int(10) NOT NULL DEFAULT '0'
 products: UPDATED datetime
 products: QTY int(10) NOT NULL DEFAULT '0'
 products: MIN_QTY int(10) NOT NULL DEFAULT '0'
 products: DETAILS text
 products: DEFAULT_PRICE float DEFAULT '0' NOT NULL

 product_categories: ID int(10) unsigned NOT NULL auto_increment
 product_categories: TITLE varchar(255) NOT NULL DEFAULT ''
 product_categories: PRIORITY int(10) NOT NULL DEFAULT '0'
 product_categories: PARENT_ID int(10) NOT NULL DEFAULT '0'
 product_categories: SUB_LIST text
 product_categories: PARENT_LIST text

 shopping_list_items: ID int(10) unsigned NOT NULL auto_increment
 shopping_list_items: TITLE varchar(255) NOT NULL DEFAULT ''
 shopping_list_items: PRODUCT_ID int(10) NOT NULL DEFAULT '0'
 shopping_list_items: PRICE float DEFAULT '0' NOT NULL
 shopping_list_items: CODE varchar(255) NOT NULL DEFAULT ''
 shopping_list_items: IN_CART int(3) NOT NULL DEFAULT '0'

 product_log: ID int(10) unsigned NOT NULL auto_increment
 product_log: TITLE varchar(255) NOT NULL DEFAULT ''
 product_log: PRODUCT_ID int(10) NOT NULL DEFAULT '0'
 product_log: CODE_ID int(10) NOT NULL DEFAULT '0'
 product_log: QTY int(10) NOT NULL DEFAULT '0'
 product_log: ACTION char(10) NOT NULL DEFAULT ''
 product_log: UPDATED datetime

 product_codes: ID int(10) unsigned NOT NULL auto_increment
 product_codes: TITLE varchar(255) NOT NULL DEFAULT ''
 product_codes: CODE varchar(255) NOT NULL DEFAULT ''
 product_codes: PRODUCT_ID int(10) NOT NULL DEFAULT '0'


EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDE2LCAyMDExIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>