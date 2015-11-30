<?php
/**
 * XML parser
 *
 * Used to parse XML-structures into hashes and vice-versa
 *
 * @package framework
 * @author Serge Dzheigalo <jey@unit.local>
 * @copyright Activeunit Inc 2001-2004
 * @version 1.0
 */


/**
 * Simple XML parser
 * @category Parsers
 * @package Framework
 * @author Serge Dzheigalo <jey@unit.local>
 * @copyright 2001-2004 Activeunit Inc
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/sergejey/majordomo/blob/master/lib/xml_data.class.php
 * example:
 * $test="<object><name>MyName</name><phone>(123)123456</phone></object>";
 * $xml=new xml_data(); // or $xml->set($test[0]);
 * $xml->string
 * $xml->hash
 */
class xml_data
{
   var $string; // string-style xml data
   var $hash;   // HASH-style xml dat
   var $ndx;    // temporary variable

   /**
    * Summary of __construct
    * @param mixed $in Input parameter
    * @return void
    */
   public function __construct($in = "")
   {
      // class constructor
      if ($in == "") return;
      
      if (is_array($in))
      {
         $this->hash   = $in;
         $this->string = $this->toXML($in);
      }
      else
      {
         $this->string = $in;
         $this->hash   = $this->fromXML($in);
      }
   }

   /**
    * Summary of set
    * @param mixed $in Input parameter
    * @return void
    */
   public function set($in = "")
   {
      // set data to object
      if ($in == "") return;
      
      if (is_array($in))
      {
         $this->hash   = $in;
         $this->string = $this->toXML($in);
      }
      else
      {
         $this->string = $in;
         $this->hash   = $this->fromXML($in);
      }
   }

   /**
    * Create XML-string from hash-data
    * @param mixed $hash  Hash
    * @param mixed $level Level (default 0)
    * @return string
    */
   public function toXML($hash, $level = 0)
   {
      // converts from hash to xml
      $res = "";

      if (!is_array($hash))
         return $res;

      foreach ($hash as $k => $v)
      {
         if (substr($k, -5) != "_list")
         {
            // skip NAME_list variables
            if ((is_array($v)) && isset($v[0]))
            {
               // array
               if (isset($hash[$k . "_list"]))
               {
                  $v = $hash[$k . "_list"];
               }

               $vCnt = count($v);
               for ($i = 0; $i < $vCnt ;$i++)
               {
                  $res .= str_repeat(" ", $level) . "<$k>\n";
                  $res .= $this->toXML($v[$i], $level + 1);
                  $res .= str_repeat(" ", $level) . "</$k>\n";
               }
            }
            elseif ((is_array($v)) && (!isset($v[0])))
            {
               // hash
               $res .= str_repeat(" ", $level) . "<$k>\n" . $this->toXML($v, $level + 1) . str_repeat(" ", $level) . "</$k>\n";
            }
            else
            {
               // variable
               $res .= str_repeat(" ", $level) . "<$k>" . $v . "</$k>\n";
            }
         }
      }

      return $res;
   }


   /**
    * Used to create hash-data from XML-based string
    * @param mixed $raw  XML-data
    * @param mixed $prev Prev (default empty)
    * @return mixed
    */
   public function fromXML($raw, $prev = "")
   {
      // converts from xml to hash
      global $ndx;

      if ($prev == "")
      {
         $this->ndx = array();
      }

      $raw     = preg_replace('/\<\?.+?\?\>\s*/s', '', $raw); //removes xml tag
      $i       = 0;
      $xml     = array();
      $pattern = '(\s*?)<([^\?]+?)>(.*?)\1<\/\2>'; // tag-pattern

      if (preg_match_all('/' . $pattern . '/s', $raw, $matches, PREG_PATTERN_ORDER))
      {
         $matchesCnt = count($matches[0]);
         for ($m = 0; $m < $matchesCnt; $m++)
         {
            $k = $matches[2][$i];
            $v = $matches[3][$i];

            $res = $this->fromXML($v, $prev . $k . $i);

            if (isset($this->ndx["$prev" . "$k"]))
            {
               if ($ndx["$prev" . "$k"] == 0)
               {
                  $vv = $xml["$k"];

                  $xml["$k"]    = array();
                  $xml["$k"][0] = $vv;
               }

               $this->ndx["$prev" . "$k"]++;
               $xml["$k"][$this->ndx["$prev" . "$k"]] = $res;
            }
            else
            {
               $this->ndx["$prev" . "$k"] = 0;

               $xml["$k"] = $res;
            }

            $xml["$k" . "_list"][$this->ndx["$prev" . "$k"]] = $res;
            $i++;
         }
      }

      return ($i == 0) ? $raw : $xml;
   }
}

// --------------------------------------------------------------------
// RELATIVE GENERAL FUNCTIONS
// --------------------------------------------------------------------

/**
 * Convert hash to xml string
 * @param mixed $hash hash
 * @return string
 */
function toXML($hash)
{
   $tmp = new xml_data($hash);

   return $tmp->string;
}

/**
 * Convert xml string to hash
 * @param mixed $str String
 * @return array
 */
function fromXML($str)
{
   $tmp = new xml_data($str);
   
   return $tmp->hash;
}
