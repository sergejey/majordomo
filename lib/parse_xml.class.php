<?php

/**
 * Parsing XML class
 * already, class supporting a cp1251 codepage
 * key register maybe lower or upper symbol register
 * simple usage
 * $objXml=&new parse_xml( array( 'REGISTER'=>2 ) );
 * $arrTree=$objXml->xml2array( $strXml );
 * codepage is latinian, register of $arrTree is lower
 * @package framework
 * @author Rodion Konnov <jey@unit.local>
 * @copyright Activeunit Inc 2001-2004
 * @version 1.4
 */


/**
 * Parser
 * @category XML_Parser
 * @package Framework
 * @author Rodion Konnov <jey@unit.local>
 * @copyright 2001-2004 Activeunit Inc
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/sergejey/majordomo/blob/master/lib/parse_xml.class.php
 */
class parse_xml
{
   /**
    * Summary of __construct
    * @param mixed $arrSetting Settings
    */
   public function __construct($arrSetting = '')
   {
      // default - lat; 1 - cp1251(winrus)
      $this->setting['CODING']   = (isset($arrSetting['CODING'])) ? $arrSetting['CODING'] : 0;
      // default - upper; 2 - lower
      $this->setting['REGISTER'] = (isset($arrSetting['REGISTER'])) ? $arrSetting['REGISTER'] : 1;
   }

   /**
    * Convert xml-string to array
    * @param mixed $strXml XML string
    * @return array
    */
   public function xml2array($strXml)
   {
      $this->mode = 'xml2array';
      
      if (!preg_match('/^<xml>/i', $strXml))
      {
         $strXml = '<' . $this->ApplyTagRegister('xml') . '>' . $strXml . '</' . $this->ApplyTagRegister('xml') . '>';
      }

      $hdlParser = xml_parser_create();

      xml_parser_set_option($hdlParser, XML_OPTION_SKIP_WHITE, 1);
      xml_parse_into_struct($hdlParser, $strXml, $arrVals, $arrIndex);
      xml_parser_free($hdlParser);
      
      $arrTree = array();
      $i       = 0;
      $arrTree = $this->fromXml($arrVals, $i);

      return $arrTree;
   }

   /**
    * Summary of fromXml
    * @param mixed $arrVals Array values
    * @param mixed $i       Iterator
    * @return array|void
    */
   public function fromXml($arrVals, &$i)
   {
      $arrChildren = array();
      
      if ($arrVals[$i]['value'])
      {
         array_push($arrChildren, $arrVals[$i]['value']);
      }

      $strPrevTag = '';
      
      while (++$i < count($arrVals))
      {
         switch ($arrVals[$i]['type'])
         {
            case 'cdata':
               array_push($arrChildren, $arrVals[$i]['value']);
               break;
            case 'complete':
               $arrVals[$i]['value'] = $this->ApplyValueCoding($arrVals[$i]['value']);
               $strKey               = $this->ApplyTagRegister($arrVals[$i]['tag']);
               
               // create elements
               if ($arrVals[$i]['tag'] == $arrVals[$i + 1]['tag'] || $arrVals[$i]['tag'] == $arrVals[$i - 1]['tag'])
               {
                  $arrChildren[][$strKey] = $arrVals[$i]['value'];
               }
               else
               {
                  $arrChildren[$strKey] = $arrVals[$i]['value'];
               }
               break;
            case 'open':
               $strKey = $this->ApplyTagRegister($arrVals[$i]['tag']);
               
               //restartindex on unique tag-name
               $j++;

               if ($strPrevTag <> $arrVals[$i]['tag'])
               {
                  $j = 0;
                  
                  $strPrevTag = $arrVals[$i]['tag'];
               }
               
               // create elements
               $arrChildren[$strKey][$j] = $this->fromXml($arrVals, $i);
               break;
            case 'close':
               return $arrChildren;
         }
      }
   }

   /**
    * Summary of array2xml
    * @param mixed $arrTree Array tree
    * @return string
    */
   public function array2xml($arrTree)
   {
      $this->mode = 'array2xml';
      $strXml     = $this->toXml($arrTree);

      $result = '<' . $this->ApplyTagRegister('xml') . '>' . $strXml . '</' . $this->ApplyTagRegister('xml') . '>';

      return $result;
   }

   /**
    * Summary of toXml
    * @param mixed $arrTree Array tree
    * @param mixed $strKey  Key
    * @return string
    */
   public function toXml($arrTree, $strKey)
   {
      foreach ($arrTree as $k => $v)
      {
         if (is_array($v))
         {
            if (is_int($k))
            {
               if (isset($strKey))
               {
                  $strXml .= '<' . $strKey . '>' . $this->toXml($v) . '</' . $strKey . '>';
               }
               else
               {
                  $strXml .= $this->toXml($v);
               }
            }
            else
            {
               $strXml .= $this->toXml($v, $k);
            }
         }
         else
         {
            $strXml .= '<' . $k . '>' . $this->ApplyValueCoding(htmlSpecialChars($v)) . '</' . $k . '>';
         }
      }

      return $strXml;
   }

   /***   hidden-system mеthods   ***/
   /**
    * Summary of ApplyTagRegister
    * @param mixed $strTag Tag
    * @return mixed
    */
   public function ApplyTagRegister($strTag)
   {
      if (1 == $this->setting['REGISTER'])
      {
         return strToUpper($strTag);
      }
      elseif (2 == $this->setting['REGISTER'])
      {
         return strToLower($strTag);
      }
      else
      {
         return $strTag;
      }
   }

   /**
    * Summary of ApplyValueCoding
    * @param mixed $strValue String value
    * @return mixed
    */
   public function ApplyValueCoding($strValue)
   {
      if (1 == $this->setting['CODING'])
      {
         if ('array2xml' == $this->mode)
         {
            return $this->win2utf($strValue);
         }
         elseif ('xml2array' == $this->mode)
         {
            return $this->utf2win($strValue);
         }
      }
      else
      {
         return $strValue;
      }
   }

   /**
    * Convert utf to win
    * @param mixed $s String
    * @return string
    */
   public function utf2win($s)
   {
      $out    = $c1 = '';
      $byte2  = false;
      $sLengh = strlen($s);

      for ($c = 0; $c < $sLengh; $c++ )
      {
         $i = ord($s[$c]);
         
         if ($i <= 127)
            $out .= $s[$c];
         
         if ($byte2)
         {
            $new_c2 = ($c1 & 3) * 64 + ($i & 63);
            $new_c1 = ($c1 >> 2) & 5;
            $new_i  = $new_c1 * 256 + $new_c2;
            
            if ($new_i == 1025)
            {
               $out_i = 168;
            }
            else
            {
               if ($new_i == 1105)
               {
                  $out_i = 184;
               }
               else
               {
                  $out_i = $new_i - 848;
               }
            }

            $out  .= chr($out_i);
            $byte2 = false;
         }

         if (($i >> 5) == 6)
         {
            $c1    = $i;
            $byte2 = true;
         }
      }

      return $out;
   }

   /**
    * Convert win to utf
    * @param mixed $str String
    * @return string
    */
   public function win2utf($str)
   {
      $utf = '';

      $strLength = strlen($str);

      for ($i = 0; $i < $strLength; $i++)
      {
         $donotrecode = false;

         $c = ord(substr($str, $i, 1));

         if ($c == 0xA8) $res            = 0xD081;
         elseif ($c == 0xB8) $res        = 0xD191;
         elseif ($c < 0xC0) $donotrecode = true;
         elseif ($c < 0xF0) $res         = $c + 0xCFD0;
         else $res                       = $c + 0xD090;

         $utf .= ($donotrecode) ? chr($c) : (chr($res >> 8) . chr($res & 0xff));
      }

      return $utf;
   }
}
