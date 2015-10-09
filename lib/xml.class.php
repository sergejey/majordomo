<?php
/*
* @version 0.2
*/


/**
 * Summary of GetChildren
 * @param mixed $vals Val
 * @param mixed $i    Iterator
 * @return array
 */
function GetChildren($vals, &$i)
{
   $children = array();
 
   while (++$i < sizeof($vals))
   {
      // compair type
      switch ($vals[$i]['type'])
      {
         case 'cdata':
            $children[] = $vals[$i]['value'];
            break;
         case 'complete':
            $children[] = array('tag'        => $vals[$i]['tag'],
                                'attributes' => $vals[$i]['attributes'],
                                'value'      => $vals[$i]['value']
                               );
            break;
         case 'open':
            $children[] = array('tag'        => $vals[$i]['tag'],
                                'attributes' => $vals[$i]['attributes'],
                                'value'      => $vals[$i]['value'],
                                'children'   => GetChildren($vals, $i)
                               );
            break;
         case 'close':
            return $children;
      }
   }
}

/**
 * Summary of GetXMLTree
 * @param mixed $data Data
 * @return array[]
 */
function GetXMLTree($data)
{
   // $data = implode('', file($file));
   // by: waldo@wh-e.com - trim space around tags not within
   //$data = eregi_replace(">"."[[:space:]]+"."<","><",$data);
   $data = preg_replace('/>\s+</', '><', $data);
   
   // XML functions
   $p = xml_parser_create();
 
   // by: anony@mous.com - meets XML 1.0 specification
   xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
   xml_parse_into_struct($p, $data, $vals, $index);
   xml_parser_free($p);
 
   $i      = 0;
   $tree   = array();
   $tree[] = array('tag'        => $vals[$i]['tag'],
                   'attributes' => $vals[$i]['attributes'],
                   'value'      => $vals[$i]['value'],
                   'children'   => GetChildren($vals, $i)
                  );
   return $tree;
}

/**
 * Convert XMLTree to Array
 * @param mixed $data Data
 * @return array
 */
function XMLTreeToArray($data)
{
   $res   = array();
   $total = count($data);

   for ($i = 0; $i < $total; $i++)
   {
      if (!isset($res[$data[$i]['tag']]))
      {
         $res[$data[$i]['tag']] = array();
         
         $elem = &$res[$data[$i]['tag']];
      }
      elseif (!is_array($res[$data[$i]['tag']][0]))
      {
         $tmp = $res[$data[$i]['tag']];
         
         $res[$data[$i]['tag']]    = array();
         $res[$data[$i]['tag']][0] = $tmp;
         $res[$data[$i]['tag']][]  = array();
         
         $elem = &$res[$data[$i]['tag']][count($res[$data[$i]['tag']]) - 1];
      }
      else
      {
         $elem = array();
         
         $res[$data[$i]['tag']][] = &$elem;
      }
  
      if (is_array($data[$i]['attributes']))
      {
         foreach ($data[$i]['attributes'] as $k => $v)
         {
            $elem[$k] = $v;
         }
      }
  
      if ($data[$i]['value'])
      {
         $elem['textvalue'] = $data[$i]['value'];
      }
  
      if (is_array($data[$i]['children']))
      {
         $children = XMLTreeToArray($data[$i]['children']);
         
         foreach ($children as $k => $v)
         {
            $elem[$k] = $v;
         }
      }
  
      unset($elem);
   }
 
   return $res;
}
