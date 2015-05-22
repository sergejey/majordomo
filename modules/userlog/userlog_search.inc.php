<?php
/*
 * @version 0.1 (auto-set)
 */

global $session;
global $added_to;
global $added_from;
global $save_qry;
global $sortby;

if ($this->owner->name == 'panel')
   $out['CONTROLPANEL'] = 1;

$qry = "1";

// search filters
if (isset($this->user_id))
{
   $user_id = $this->user_id;
   $qry .= " AND USER_ID = '" . $this->user_id . "'";
}
else
{
   global $user_id;
}

if ($added_to)
   $qry .= " AND TO_DAYS(ADDED) <= TO_DAYS('" . toDBDate($added_to) . "')";

if ($added_from)
   $qry .= " AND TO_DAYS(ADDED) >= TO_DAYS('" . toDBDate($added_from) . "')";

// QUERY READY
if ($save_qry)
   $qry = $session->data['userlog_qry'];
else
   $session->data['userlog_qry'] = $qry;

if (!$qry) $qry = "1";

// FIELDS ORDER

if (!$sortby)
{
   $sortby = $session->data['userlog_sort'];
}
else
{
   if ($session->data['userlog_sort'] == $sortby) 
   {
      if (is_integer(strpos($sortby, ' DESC'))) 
         $sortby = str_replace(' DESC', '', $sortby);
      else
         $sortby = $sortby . " DESC";
   }
   
   $session->data['userlog_sort'] = $sortby;
}

if (!$sortby) 
   $sortby = "userlog.ID DESC";

$out['SORTBY'] = $sortby;

// SEARCH RESULTS
$res = SQLSelect("SELECT userlog.*, admin_users.NAME, DATE_FORMAT(ADDED, '%m/%d/%Y %H:%i:%s') as ADDED 
                    FROM userlog, admin_users 
                   WHERE userlog.USER_ID = admin_users.ID 
                     AND $qry 
                   ORDER BY $sortby");

if ($res[0]['ID'])
{
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $out['RESULT']=$res;
}

?>