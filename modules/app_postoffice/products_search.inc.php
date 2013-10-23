<?php
/*
* @version 0.2 (wizard)
*/

 global $ajax;
 global $op;
 global $id;
 global $shopping;
 if ($shopping) {
  $out['SHOPPING']=1;
 }


 if ($ajax) {

  header ("HTTP/1.0: 200 OK\n");
  header ('Content-Type: text/html; charset=utf-8');

  if ($op=='addtocart') {
   $this->addToList($id);
   echo "OK";
  }

  if ($op=='removefromcart') {
   $this->removeFromList($id);
   echo "OK";
  }

  if ($op=='incart') {
   SQLExec("UPDATE products SET QTY=QTY+1 WHERE ID='".(int)$id."'");
   SQLExec("UPDATE shopping_list_items SET IN_CART=1 WHERE PRODUCT_ID='".(int)$id."'");
   echo "OK";
   $rec=SQLSelectOne("SELECT * FROM products WHERE ID='".(int)$id."'");
   say('Добавлено в корзину '.$rec['TITLE']);
  }

  if ($op=='notincart') {
   SQLExec("UPDATE products SET QTY=QTY-1 WHERE ID='".(int)$id."'");
   SQLExec("UPDATE shopping_list_items SET IN_CART=0 WHERE PRODUCT_ID='".(int)$id."'");
   echo "OK";
  }



  if ($op=='plus') {
   $rec=SQLSelectOne("SELECT * FROM products WHERE ID='".(int)$id."'");
   $rec['UPDATED']=date('Y-m-d H:i:s');
   $rec['QTY']+=1;
   if ($rec['EXPIRE_DEFAULT']>0) {
    $rec['EXPIRE_DATE']=date('Y-m-d H:i:s', (time()+$rec['EXPIRE_DEFAULT']*60*60*24));
   } else {
    $rec['EXPIRE_DATE']='0000-00-00 00:00:00';
   }

   SQLUpdate('products', $rec);

    $log=array();
    $log['UPDATED']=date('Y-m-d H:i:s');
    $log['PRODUCT_ID']=$rec['ID'];
    $log['TITLE']=$rec['TITLE'];
    $log['CODE_ID']=0;
    $log['QTY']=1;
    if ($log['QTY']>0) {
     $log['ACTION']='added'; // added
    } elseif ($log['QTY']<0) {
     $log['ACTION']='removed'; // removed
    } else {
     $log['ACTION']='updated'; // removed
    }
    $log['ID']=SQLInsert('product_log', $log);

   echo $rec['QTY'];
  }
  if ($op=='minus') {
   $rec=SQLSelectOne("SELECT * FROM products WHERE ID='".(int)$id."'");
   $rec['UPDATED']=date('Y-m-d H:i:s');
   $rec['QTY']-=1;
   if ($rec['QTY']>=0) {

    $log=array();
    $log['UPDATED']=date('Y-m-d H:i:s');
    $log['PRODUCT_ID']=$rec['ID'];
    $log['TITLE']=$rec['TITLE'];
    $log['CODE_ID']=0;
    $log['QTY']=-1;
    if ($log['QTY']>0) {
     $log['ACTION']='added'; // added
    } elseif ($log['QTY']<0) {
     $log['ACTION']='removed'; // removed
    } else {
     $log['ACTION']='updated'; // removed
    }
    $log['ID']=SQLInsert('product_log', $log);

    SQLUpdate('products', $rec);
   } else {
    $rec['QTY']=0;
   }
   echo $rec['QTY'];
  }
  exit;
 }

 global $code;
 global $code_title;

 if ($code!='') {
  $code=trim($code);
  $out['CODE']=$code;
  if (preg_match('/^\d+$/is', $code)) {
   $out['IS_CODE']=1;
   $out['TITLE']='';
  } else {
   $out['TITLE']=$out['CODE'];
  }

  $product_qry='';
  if ($out['IS_CODE']) {
   $tmp=SQLSelectOne("SELECT * FROM product_codes WHERE CODE LIKE '".DBSafe($code)."'");
   if ($tmp['ID']) {
    $product_qry="ID=".$tmp['PRODUCT_ID'];
    $out['PRODUCT_ID']=$tmp['PRODUCT_ID'];
   }
  } else {
   $product_qry="TITLE LIKE '".DBSafe($out['CODE'])."'";
   $suggestions=SQLSelect("SELECT ID, TITLE FROM products WHERE TITLE LIKE '%".DBSafe($code)."%'");
   if ($suggestions[0]['ID']) {
    $total=count($suggestions);
    for($i=0;$i<$total;$i++) {
     $suggestions[$i]['TITLE_HTML']=htmlspecialchars($suggestions[$i]['TITLE']);
    }
    $out['SUGGESTIONS']=$suggestions;
   }
  }

  if ($product_qry && !$code_title) {
   $tmp=SQLSelectOne("SELECT * FROM products WHERE ".$product_qry);
   if ($tmp['ID']) {
    $out['TITLE']=$tmp['TITLE'];
    $out['PRODUCT_ID']=$tmp['ID'];
    $out['CATEGORY_ID']=$tmp['CATEGORY_ID'];
    $out['EXPIRE']=$tmp['EXPIRE_DEFAULT'];
    $out['FOUND']=1;
   }
  }


  if ($out['IS_CODE'] && !$out['PRODUCT_ID']) {
   //getting from the web
   $tmp=@getURL('http://www.goodsmatrix.ru/goods/'.$code.'.html', 600000);
   if (preg_match('/<span id="_ctl0_ContentPH_GoodsName".+>(.+?)<\/span>/', $tmp, $m)) {
    $out['CODE_TITLE']=mb_convert_encoding($m[1], "UTF-8", "windows-1251");
    $out['CODE_TITLE_HTML']=mb_convert_encoding(htmlspecialchars($m[1]), "UTF-8", "windows-1251");
   }
  }

  if (!$out['SHOPPING']) {
    $out['QTY']=1;
  }

  if ($this->mode=='add_product') {
   global $title;
   global $qty;
   global $expire_days;
   global $expire_date;
   global $category_id;
   global $product_id;
   global $qty_total;
   global $new_category;

   if ($expire_date) {
    $out['EXPIRE_DATE']=$expire_date;
   }

   if (!$product_id) { // && $out['IS_CODE']
    $old_product=SQLSelectOne("SELECT ID FROM products WHERE TITLE LIKE '".DBSafe($title)."'");
    if ($old_product['ID']) {
     $product_id=$old_product['ID'];
    }
   }

   if ($product_id) {

    //existing product
    $out['PRODUCT_ID']=$product_id;

    $rec=SQLSelectOne("SELECT * FROM products WHERE ID='".(int)$product_id."'");
    if (!$rec['ID']) {
     $this->redirect();
    }
    $old_qty=$rec['QTY'];
    if ($qty_total) {
     $rec['QTY']=$qty;
    } else {
     $rec['QTY']+=$qty;
    }

    if ($qty>0) {
     if ($expire_days) {
      $rec['EXPIRE_DATE']=date('Y-m-d H:i:s', (time()+$expire_days*60*60*24));
     } elseif ($rec['EXPIRE_DEFAULT']) {
      $rec['EXPIRE_DATE']=date('Y-m-d H:i:s', (time()+$rec['EXPIRE_DEFAULT']*60*60*24));
     } else {
      $rec['EXPIRE_DATE']='0000-00-00 00:00:00';
     }

     if ($expire_date) {
      $rec['EXPIRE_DATE']=toDBDate($expire_date);
     }

    }

    $rec['UPDATED']=date('Y-m-d H:i:s');
    if ($category_id) {
     $rec['CATEGORY_ID']=$category_id;
    }

    if ($title) {
     $rec['TITLE']=$title;
    }

    SQLUpdate('products', $rec);

    if ($out['IS_CODE']) {
     $code_rec=SQLSelectOne("SELECT * FROM product_codes WHERE CODE LIKE '".$code."' AND PRODUCT_ID='".$rec['ID']."'");
     if ($code_rec['ID']) {
      // existing code
     } else {
      $code_rec=array();
      $code_rec['CODE']=$code;
      if ($code_title) {
       $code_rec['TITLE']=$code_title;
      } else { 
       $code_rec['TITLE']=$title;
      }
      $code_rec['PRODUCT_ID']=$rec['ID'];
      $code_rec['ID']=SQLInsert('product_codes', $code_rec);
     }
    }

  } else {
   // brand new product
   $rec=array();
   $rec['TITLE']=$title;
   if ($category_id) {
    $rec['CATEGORY_ID']=$category_id;
   }
   if ($new_category) {
    $category_rec=array();
    $category_rec['TITLE']=$new_category;
    $category_rec['ID']=SQLInsert('product_categories', $category_rec);
    $rec['CATEGORY_ID']=$category_rec['ID'];
   }
   $old_qty=0;
   $rec['QTY']=(int)$qty;
   $rec['EXPIRE_DEFAULT']=(int)$expire_days;
   if ($rec['EXPIRE_DEFAULT']) {
    $rec['EXPIRE_DATE']=date('Y-m-d H:i:s', (time()+$rec['EXPIRE_DEFAULT']*60*60*24));
   }
   $rec['UPDATED']=date('Y-m-d H:i:s');
   $rec['ID']=SQLInsert('products', $rec);

    if ($out['IS_CODE']) {
      $code_rec=array();
      $code_rec['CODE']=$code;
      if ($code_title) {
       $code_rec['TITLE']=$code_title;
      } else { 
       $code_rec['TITLE']=$title;
      }
      $code_rec['PRODUCT_ID']=$rec['ID'];
      $code_rec['ID']=SQLInsert('product_codes', $code_rec);
    }

  }




  if ($rec['ID']) {

    $log=array();
    $log['UPDATED']=date('Y-m-d H:i:s');
    $log['PRODUCT_ID']=$rec['ID'];
    $log['TITLE']=$rec['TITLE'];
    $log['CODE_ID']=$code_rec['ID'];
    $log['QTY']=$old_qty-$req['QTY'];
    if ($log['QTY']>0) {
     $log['ACTION']='added'; // added
    } elseif ($log['QTY']<0) {
     $log['ACTION']='removed'; // removed
    } else {
     $log['ACTION']='updated'; // removed
    }
    $log['ID']=SQLInsert('product_log', $log);

  }

  if ($out['SHOPPING']) {

    SQLExec("DELETE FROM shopping_list_items WHERE PRODUCT_ID='".(int)$rec['ID']."'");
    $item=array();
    $item['PRODUCT_ID']=$rec['ID'];
    $item['TITLE']=$rec['TITLE'];
    $item['IN_CART']=0;
    SQLInsert('shopping_list_items', $item);

   $this->redirect("?shopping=1");
  } else {
   $this->redirect("?category_id=".$rec['CATEGORY_ID']);
  }
  

  }


 }

 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  //searching 'TITLE' (varchar)

  if ($out['PRODUCT_ID']) {
   $qry.=" AND products.ID='".($out['PRODUCT_ID'])."'";
  }

  global $title;

  if (!$out['IS_CODE'] && $out['CODE']) {
   $title=$out['CODE'];
  }


  if ($title!='') {
   $qry.=" AND products.TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
  if (IsSet($this->category_id)) {
   $category_id=$this->category_id;
  } else {
   global $category_id;
  }

  if ($category_id) {
   $qry.=" AND products.CATEGORY_ID='".(int)$category_id."'";
   $out['CATEGORY_ID']=$category_id;
  }

  global $expired;
  if ($expired) {
   $qry.=" AND TO_DAYS(EXPIRE_DATE)<=TO_DAYS(NOW()) AND QTY>0";
   $out['EXPIRED']=1;
  }

  global $missing;
  if ($missing) {
   $qry.=" AND (QTY<MIN_QTY OR QTY=0)";
   $out['MISSING']=1;
  }

  if ($shopping) {
   $qry.=" AND shopping_list_items.ID>0";
   $out['SHOPPING']=1;
  }

  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['products_qry'];
  } else {
   $session->data['products_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['products_sort'];
  } else {
   if ($session->data['products_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['products_sort']=$sortby;
  }


  $sortby="CATEGORY_ID, TITLE";
  if ($out['SHOPPING']) {
   $sortby="IN_CART, CATEGORY_ID, TITLE";
  }


  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT products.*, shopping_list_items.IN_CART, (TO_DAYS(EXPIRE_DATE)-TO_DAYS(NOW())) as EXPIRE_TERM, product_categories.TITLE as CATEGORY_TITLE FROM products LEFT JOIN product_categories ON products.CATEGORY_ID=product_categories.ID LEFT JOIN shopping_list_items ON products.ID=shopping_list_items.PRODUCT_ID WHERE $qry ORDER BY $sortby");
  if ($res[0]['ID']) {
   //paging($res, 50, $out); // search result paging
   $total=count($res);
   $old_category_id=0;
   for($i=0;$i<$total;$i++) {
    // some action for every record if required

    $tmp=SQLSelectOne("SELECT ID FROM shopping_list_items WHERE PRODUCT_ID='".$res[$i]['ID']."'");
    if ($tmp['ID']) {
     $res[$i]['INCART']=1;
    } else {
     $res[$i]['INCART']=0;
    }


    if ($out['SHOPPING']) {
     if ($res[$i]['IN_CART']!=$old_category_id) {
      $res[$i]['CATEGORY_TITLE']='Cart';
      $old_category_id=$res[$i]['IN_CART'];
      $res[$i]['NEW_CATEGORY']=1;
     }
    } else {
     if ($res[$i]['CATEGORY_ID']!=$old_category_id) {
      $old_category_id=$res[$i]['CATEGORY_ID'];
      $res[$i]['NEW_CATEGORY']=1;
     }
    }

    if ($res[$i]['EXPIRE_TERM']>15) {
     unset($res[$i]['EXPIRE_TERM']);
    }

    if ($res[$i]['EXPIRE_TERM']<=0) {
     $res[$i]['EXPIRED']=1;
    }
    if ($res[$i]['MIN_QTY']>0 && $res[$i]['QTY']<$res[$i]['MIN_QTY']) {
     $res[$i]['RECOMMENDED']=$res[$i]['MIN_QTY'];
    }


   }
   $out['RESULT']=$res;
  }

  global $all;
  if ($all) {
   $out['ALL']=1;
  }

  if ($qry=="1" && !$out['ALL']) {
   $cats=SQLSelect("SELECT ID, PARENT_ID, TITLE FROM product_categories WHERE 1 ORDER BY ID,  TITLE");
   $total=count($cats);
   for($i=0;$i<$total;$i++) {
    $cats[$i]['TOTAL']=current(SQLSelectOne("SELECT COUNT(*) as TOTAL FROM products WHERE CATEGORY_ID='".$cats[$i]['ID']."'"));
   }
   $cats=$this->buildTree_product_categories($cats);
   $out['CATS']=$cats;
  }

  $out['CATEGORIES']=SQLSelect("SELECT ID, TITLE FROM product_categories ORDER BY PARENT_ID, ID, TITLE");

  if ($category_id) {
   $total=count($out['CATEGORIES']);
   for($i=0;$i<$total;$i++) {
    if ($out['CATEGORIES'][$i]['ID']==$category_id && $out['CATEGORIES'][$i+1]['ID']) {
     $out['NEXT_CATEGORY_ID']=$out['CATEGORIES'][$i+1]['ID'];
     $out['NEXT_CATEGORY_TITLE']=$out['CATEGORIES'][$i+1]['TITLE'];
    }

    if ($out['CATEGORIES'][$i]['ID']==$category_id && $out['CATEGORIES'][$i-1]['ID']) {
     $out['PREV_CATEGORY_ID']=$out['CATEGORIES'][$i-1]['ID'];
     $out['PREV_CATEGORY_TITLE']=$out['CATEGORIES'][$i-1]['TITLE'];
    }

   }
  }

?>