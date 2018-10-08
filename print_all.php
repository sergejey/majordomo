<?php
/**
* Test script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.3
*/

include_once("./config.php");
include_once("./lib/loader.php");

// get settings
include_once("./load_settings.php");

header('Content-Type: text/html; charset=utf-8');

$sqlQuery = "SELECT COUNT(*) as TOTAL
               FROM commands
              WHERE 1";

$tmp = SQLSelectOne($sqlQuery);

if ($tmp['TOTAL'])
   $out['HAVE_MENU'] = 1;

$sqlQuery = "SELECT COUNT(*) as TOTAL
               FROM scripts
              WHERE 1";

$tmp = SQLSelectOne($sqlQuery);

if ($tmp['TOTAL'])
{
   $out['HAVE_SCRIPTS'] = 1;

   $sqlQuery = "SELECT scripts.*, script_categories.TITLE as CATEGORY
                  FROM scripts
                  LEFT JOIN script_categories ON scripts.CATEGORY_ID = script_categories.ID
                 ORDER BY script_categories.TITLE, scripts.TITLE";

   $res   = SQLSelect($sqlQuery);
   $total = count($res);

   for ($i = 0; $i < $total; $i++)
   {
      if (!$res[$i]['CATEGORY'])
         $res[$i]['CATEGORY'] = '...';
    
      $res[$i]['DESCRIPTION'] = nl2br(htmlspecialchars($res[$i]['DESCRIPTION']));
      $res[$i]['CODE']        = (htmlspecialchars($res[$i]['CODE']));

      if ($res[$i]['CATEGORY'] != $old_category)
      {
         $out['TOTAL_CATEGORIES']++;
         
         $old_category            = $res[$i]['CATEGORY'];
         $res[$i]['NEW_CATEGORY'] = 1;
      }

      if ($i == $total - 1)
         $res[$i]['LAST'] = 1;
   }
   
   $out['SCRIPTS'] = $res;
}

$tmp = SQLSelectOne("SELECT COUNT(*) as TOTAL FROM classes WHERE 1");

if ($tmp['TOTAL'])
{
   $out['HAVE_OBJECTS'] = 1;

   $sqlQuery = "SELECT *
                  FROM classes
                 WHERE 1
                 ORDER BY PARENT_ID, TITLE";

   $classes = SQLSelect($sqlQuery);
   $total   = count($classes);
   
   for ($i = 0; $i < $total; $i++)
   {
      if ($classes[$i]['PARENT_ID'])
      {
         $sqlQuery = "SELECT TITLE
                        FROM classes
                       WHERE 1
                         AND ID = '" . $classes[$i]['PARENT_ID'] . "'";

         $classes[$i]['PARENT_TITLE'] = current(SQLSelectOne($sqlQuery));
      }

      $sqlQuery = "SELECT *
                     FROM properties
                    WHERE 1
                      AND CLASS_ID  = '" . $classes[$i]['ID'] . "'
                      AND OBJECT_ID = 0";

      $properties = SQLSelect($sqlQuery);
    
      if ($properties[0]['ID'])
         $classes[$i]['PROPERTIES'] = $properties;

      $sqlQuery = "SELECT *
                     FROM methods
                    WHERE 1
                      AND CLASS_ID  = '" . $classes[$i]['ID'] . "'
                      AND OBJECT_ID = 0";
      
      $methods = SQLSelect($sqlQuery);
    
      if ($methods[0]['ID'])
      {
         $totalm = count($methods);
       
         for ($im = 0; $im < $totalm; $im++)
            $methods[$im]['CODE'] = htmlspecialchars($methods[$im]['CODE']);

         $classes[$i]['METHODS'] = $methods;
      }
    
      $sqlQuery = "SELECT *
                     FROM objects
                    WHERE 1
                      AND CLASS_ID = '" . $classes[$i]['ID'] . "'";

      $objects = SQLSelect($sqlQuery);
    
      if ($objects[0]['ID'])
      {
         $totalo = count($objects);

         for ($io = 0; $io < $totalo; $io++)
         {
            $sqlQuery = "SELECT *
                           FROM methods
                          WHERE 1
                            AND OBJECT_ID = '" . $objects[$io]['ID'] . "'";
            
            $methods = SQLSelect($sqlQuery);

            if ($methods[0]['ID'])
            {
               $totalm = count($methods);
       
               for($im = 0; $im < $totalm; $im++)
                  $methods[$im]['CODE'] = htmlspecialchars($methods[$im]['CODE']);
       
               $objects[$io]['METHODS'] = $methods;
            }
         }
     
         $classes[$i]['OBJECTS'] = $objects;
      }
   }
   
   $out['CLASSES'] = buildTree_classes($classes);
}

$tmp = SQLSelectOne("SELECT COUNT(*) as TOTAL FROM patterns WHERE 1");

if ($tmp['TOTAL'])
{
   $out['HAVE_PATTERNS'] = 1;
   $patterns             = SQLSelect("SELECT * FROM patterns WHERE 1 ORDER BY PARENT_ID, TITLE");
   $out['PATTERNS']      = buildTree_patterns($patterns);
}

if (file_exists(DIR_MODULES . 'zwave/zwave.class.php'))
{
   $devices = SQLSelect("SELECT * FROM zwave_devices ORDER BY NODE_ID, INSTANCE_ID, TITLE");
   $total   = count($devices);
   
   for ($i = 0; $i < $total; $i++)
   {
      $sqlQuery = "SELECT *
                     FROM zwave_properties
                    WHERE DEVICE_ID = '" . $devices[$i]['ID'] . "'
                    ORDER BY COMMAND_CLASS, TITLE";

      $properties                = SQLSelect($sqlQuery);
      $devices[$i]['PROPERTIES'] = $properties;
   }

   $out['ZWAVE_DEVICES'] = $devices;
}

$template_file = DIR_TEMPLATES . "print_all.html";

$p = new parser($template_file, $out);
echo $p->result;

//registerError('custom', 'Some error details');
//$tmp=SQLSelect("SELECT FROM unknown");

/**
 * Summary of buildTree_patterns
 * @param mixed $res      array
 * @param mixed $parentID parent id
 * @param mixed $level    level
 * @return array
 */
function buildTree_patterns($res, $parentID = 0, $level = 0)
{
   $total = count($res);
   $res2  = array();

   for ($i = 0; $i < $total; $i++)
   {
      if ($res[$i]['PARENT_ID'] == $parentID)
      {
         $res[$i]['LEVEL']    = $level;
         $res[$i]['PATTERNS'] = buildTree_patterns($res, $res[$i]['ID'], ($level + 1));
         
         $res2[] = $res[$i];
      }
   }
  
   $total2 = count($res2);
  
   if ($total2)
      return $res2;
}

/**
 * Summary of buildTree_classes
 * @param mixed $res      Array
 * @param mixed $parentID Parent id
 * @param mixed $level    Level
 * @return array
 */
function buildTree_classes($res, $parentID = 0, $level = 0)
{
   $total = count($res);
   $res2  = array();

   for ($i = 0; $i < $total; $i++)
   {
      if ($res[$i]['PARENT_ID'] == $parentID)
      {
         $res[$i]['LEVEL']   = $level;
         $res[$i]['CLASSES'] = buildTree_classes($res, $res[$i]['ID'], ($level + 1));
      
         if (!is_array($res[$i]['CLASSES']))
            unset($res[$i]['CLASSES']);

         if (!$res[$i]['CLASSES'] && !$res[$i]['OBJECTS'])
            $res[$i]['CAN_DELETE'] = 1;
    
         $res2[] = $res[$i];
      }
   }

   $total2 = count($res2);
   
   if ($total2)
      return $res2;
}
