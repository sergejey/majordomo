<?php


// BEGIN: begincut endcut placecut
if (preg_match_all('/<!--placecut (\w+?)-->/is', $result, $matches))
{
   $matchesCount = count($matches[1]);
   for ($i = 0; $i < $matchesCount; $i++)
   {
      $block = $matches[1][$i];
      if (preg_match('/<!--begincut ' . $block . ' -->(.*?)<!--endcut ' . $block . '-->/is', $result, $matches2))
      {
         $result = str_replace($matches[0][$i], $matches2[1], $result);
         $result = str_replace($matches2[0], '', $result);
      }
   }
}
// END: begincut endcut placecut

// BEGIN: filter output
if (isset($filterblock) && $filterblock != '')
{
   $matchPattern = '/<!--begin_data \[' . $filterblock . '\]-->(.*?)<!--end_data \[' . $filterblock . '\]-->/is';
   preg_match($matchPattern, $result, $match);
   $result = $match[1];
}
// END: filter output

// GLOBALS
$result = preg_replace('/%rand%/is', rand(), $result);

if (preg_match_all('/%(\w{2,}?)\.(\w{2,}?)%/isu', $result, $m))
{
   $total = count($m[0]);
   
   for ($i = 0; $i < $total; $i++)
   {
      $result = str_replace($m[0][$i], getGlobal($m[1][$i] . '.' . $m[2][$i]), $result);
   }
}

if (preg_match_all('/%(\w{2,}?)\.(\w{2,}?)\|(\d+)%/isu', $result, $m))
{
  if (!defined('DISABLE_WEBSOCKETS') || DISABLE_WEBSOCKETS==0)
  {
   $tracked_properties=array();
   $total = count($m[0]);
   $seen=array();
   $sub_js='';
   for ($i = 0; $i < $total; $i++)
   {
      $var=mb_strtolower($m[1][$i] . '.' . $m[2][$i], 'UTF-8');
      if (!$seen[$var]) 
      {
       $tracked_properties[]=$var;
      }
      $seen[$var]=1;
      $id = 'var_' . preg_replace('/\W/', '_', $var);
      $sub_js.="if (obj[i]['PROPERTY'].toLowerCase()=='$var') {\$('.$id').html(obj[i]['VALUE']);$.publish('$var.updated', obj[i]['VALUE']);}\n";
      $result = str_replace($m[0][$i], '<span class="'.$id.'">'.getGlobal($var).'</span>', $result);
   }
   $js="<script language='javascript'>\$.subscribe('wsConnected', function (_) {
         var payload;
         payload = new Object();
         payload.action = 'Subscribe';
         payload.data = new Object();
         payload.data.TYPE='properties';
         payload.data.PROPERTIES='".implode(',', $tracked_properties)."';
         console.log('Subscription to properties sent.');
         wsSocket.send(JSON.stringify(payload));
        });\n";
   $js.="function processPropertiesUpdate(data) {
         var obj=jQuery.parseJSON(data);
         var objCnt = obj.length;
           if (objCnt) {
              for(var i=0;i<objCnt;i++) {
               $sub_js
              }
           }
        }";
   $js.="\$.subscribe('wsData', function (_, response) {
          if (response.action=='properties') {
           processPropertiesUpdate(response.data);
          }
          });"; 
   $js.='</script>';

  $result=str_replace('<body', $js.'<body', $result);

  } else {

   $total = count($m[0]);
   $seen  = array();
   
   for ($i = 0; $i < $total; $i++)
   {
      $var      = $m[1][$i] . '.' . $m[2][$i];
      $interval = (int)$m[2][$i] * 1000;

      if (!$interval)
         $interval = 10000;
      
      $id = 'var_' . preg_replace('/\W/', '_', $var) . $seen[$var];
      $seen[$var]++;

      $scriptReplace  = '<span id="' . $id . '">...</span>';
      $scriptReplace .= '<script type="text/javascript">';
      $scriptReplace .= 'ajaxGetGlobal("' . $var . '", "' . $id . '", ' . $interval . ');';
      $scriptReplace .= '</script>';

      $result = str_replace($m[0][$i], $scriptReplace, $result);
   }

  }

}
// END GLOBALS

// BEGIN: language constants
if (preg_match_all('/&\#060\#LANG_(.+?)\#&\#062/is', $result, $matches))
{
   $total = count($matches[0]);
   
   for ($i = 0; $i < $total; $i++)
   {
      /*
      if (preg_match('/value=["\']' . preg_quote($matches[0][$i]) . '["\']/is', $result))
         continue;
         */
      
      $languageConstant = 'LANG_' . $matches[1][$i];
      if (defined($languageConstant))
      {
         $result = str_replace($matches[0][$i], constant($languageConstant), $result);
      }
      else
      {
         $resultMessageHtml = 'Warning: <i>' . $languageConstant . '</i> not defined, please check dictionary file';
         echo '<b style="color:red;">' . $resultMessageHtml . '</b><br />';
      }
   }
}
// END: language constants
