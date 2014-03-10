<?php
/*
 * @version 0.1 (auto-set)
 */
function mysort_array($ar, $field = "TITLE") 
{
   $k = 1;
   while ($k > 0) 
   {
      $k = 0;
      $arrCnt = count($ar);
      for ($i = 1; $i < $arrCnt; $i++) 
      {
         if (strcmp($ar[$i-1][$field], $ar[$i][$field]) == 1)
         {
            $temp = array();
            $temp = $ar[$i-1];
            $ar[$i-1] = $ar[$i];
            $ar[$i] = $temp;
            $k++;
         }
      }
   }
   return $ar;
}
?>