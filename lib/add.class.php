<?php
/*
 * @version 0.1 (auto-set)
 */

/**
 * Summary of win2utf
 * @param mixed $in Sting in cp1251
 * @return string
 */
function win2utf($in)
{
   return iconv('windows-1251', 'utf-8', $in);
}

/**
 * Summary of utf2win
 * @param mixed $in String in utf-8
 * @return string
 */
function utf2win($in)
{
   return iconv('utf-8', 'windows-1251', $in);
}

/**
 * Summary of mysort_array
 * @param mixed $ar    Array
 * @param mixed $field Sort field (default 'TITLE')
 * @return mixed
 */
function mysort_array($ar, $field = "TITLE")
{
   $k = 1;
   while ($k > 0)
   {
      $k     = 0;
      $arCnt = count($ar);

      for ($i = 1; $i < $arCnt; $i++)
      {
         if (strcmp($ar[$i - 1][$field], $ar[$i][$field]) == 1)
         {
            $temp = array();
            $temp = $ar[$i - 1];
            
            $ar[$i - 1] = $ar[$i];
            $ar[$i]     = $temp;

            $k++;
         }
      }
   }

   return $ar;
}
