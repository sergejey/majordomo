<?php
/*
* @version 0.2 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='products';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  // step: default
  if ($this->tab=='') {
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'Category' (select)
   if (IsSet($this->category_id)) {
    $rec['CATEGORY_ID']=$this->category_id;
   } else {
   global $category_id;
   $rec['CATEGORY_ID']=$category_id;
   }
  //updating 'EXPIRE_DATE' (date)
   global $expire_date;
   $rec['EXPIRE_DATE']=toDBDate($expire_date);
  //updating 'EXPIRE_DEFAULT' (int)
   global $expire_default;
   $rec['EXPIRE_DEFAULT']=(int)$expire_default;
  //updating 'UPDATED' (datetime)
  /*
   global $updated_date;
   global $updated_minutes;
   global $updated_hours;
   $rec['UPDATED']=toDBDate($updated_date)." $updated_hours:$updated_minutes:00";
   */
  //updating 'QTY' (int)
   global $qty;
   $rec['QTY']=(int)$qty;
  //updating 'MIN_QTY' (int)
   global $min_qty;
   $rec['MIN_QTY']=(int)$min_qty;
  //updating 'DETAILS' (text)
   global $details;
   $rec['DETAILS']=$details;
  }
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
   //updating 'IMAGE' (image)
   global $image;
   global $image_name;
   global $delete_image;
   if ($image!="" && file_exists($image) && (!$delete_image)) {
     $filename=strtolower(basename($image_name));
     $ext=strtolower(end(explode(".",basename($image_name))));
     if (
         (filesize($image)<=(0*1024) || 0==0) && (Is_Integer(strpos('gif jpg png', $ext)))
        ) {
           $filename=$rec["ID"]."_image_".time().".".$ext;
           if ($rec["IMAGE"]!='') {
            @Unlink(ROOT.'./cms/products//'.$rec["IMAGE"]);
           }
           Copy($image, ROOT.'./cms/products//'.$filename);
           $rec["IMAGE"]=$filename;
           SQLUpdate($table_name, $rec);
          }
   } elseif ($delete_image) {
      @Unlink(ROOT.'./cms/products//'.$rec["IMAGE"]);
      $rec["IMAGE"]='';
      SQLUpdate($table_name, $rec);
   }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  // step: default
  if ($this->tab=='') {
  //options for 'Category' (select)
  $tmp=SQLSelect("SELECT ID, TITLE FROM product_categories ORDER BY TITLE");
  $categories_total=count($tmp);
  for($categories_i=0;$categories_i<$categories_total;$categories_i++) {
   $category_id_opt[$tmp[$categories_i]['ID']]=$tmp[$categories_i]['TITLE'];
  }
  for($i=0;$i<$categories_total;$i++) {
   if ($rec['CATEGORY_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['CATEGORY_ID_OPTIONS']=$tmp;
   if ($rec['EXPIRE_DATE']!='') {
    $rec['EXPIRE_DATE']=fromDBDate($rec['EXPIRE_DATE']);
   }
  if ($rec['UPDATED']!='') {
   $tmp=explode(' ', $rec['UPDATED']);
   $out['UPDATED_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $updated_hours=$tmp2[0];
   $updated_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$updated_minutes) {
    $out['UPDATED_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['UPDATED_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$updated_hours) {
    $out['UPDATED_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['UPDATED_HOURS'][]=array('TITLE'=>$title);
   }
  }
  }


  if ($this->tab=='codes') {
   global $new_code;
   global $new_title;
   if ($this->mode=='update') {
    $new_rec=array();
    $new_rec['TITLE']=$new_title;
    $new_rec['CODE']=$new_code;
    $new_rec['PRODUCT_ID']=$rec['ID'];
    if ($new_rec['CODE']!='') {
     SQLInsert('product_codes', $new_rec);
    }
   }


   global $delete_code;
   if ($delete_code) {
    SQLExec("DELETE FROM product_codes WHERE ID='".(int)$delete_code."' AND PRODUCT_ID=".$rec['ID']);
   }

   $codes=SQLSelect("SELECT * FROM product_codes WHERE PRODUCT_ID='".$rec['ID']."'");
   if ($codes[0]['ID']) {
    $out['CODES']=$codes;
   }
  }


   if ($this->tab=='history') {
    $logs=SQLSelect("SELECT product_log.*, product_codes.TITLE as CODE_TITLE, product_codes.CODE FROM product_log LEFT JOIN product_codes ON product_log.CODE_ID=product_codes.ID WHERE product_log.PRODUCT_ID='".$rec['ID']."' ORDER BY product_log.UPDATED DESC");
    if ($logs[0]['ID']) {
     $out['LOGS']=$logs;
    }
   }



  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);

  global $delete;
  if ($delete) {
   $this->delete_products($rec['ID']);
   $this->redirect("?");
  }

?>