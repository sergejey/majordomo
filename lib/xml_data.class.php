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


/*
 xml_data.class.php - simple XML parser

 example:
  $test="<object><name>MyName</name><phone>(123)123456</phone></object>";
  $xml=new xml_data(); // or $xml->set($test[0]);
  $xml->string
  $xml->hash

*/
 class xml_data {
  var $string; // string-style xml data
  var $hash;   // HASH-style xml dat
  var $ndx;    // temporary variable

// --------------------------------------------------------------------
  function xml_data($in="") {
  // class constructor
   if ($in=="") return;
   if (is_array($in)) {
    $this->hash=$in;
    $this->string=$this->toXML($in);
   } else {
    $this->string=$in;
    $this->hash=$this->fromXML($in);
   }
  }
/**
* @access private
*/
  function set($in="") {
  // set data to object
   if ($in=="") return;
   if (is_array($in)) {
    $this->hash=$in;
    $this->string=$this->toXML($in);
   } else {
    $this->string=$in;
    $this->hash=$this->fromXML($in);
   }
  }
/**
* Create XML-string from hash-data
*
* 
* @param mixed Hash
* @access public
*/
  function toXML($hash, $level=0) {
  // converts from hash to xml
   $res="";
   if (!Is_Array($hash)) {
    return $res;
   }
   foreach($hash as $k=>$v) {
    if (substr($k, -5)!="_list") {
    // skip NAME_list variables
     if ((Is_Array($v)) && IsSet($v[0])) {
      // array
      if (IsSet($hash[$k."_list"])) {
       $v=$hash[$k."_list"];
      }
      for($i=0;$i<count($v);$i++) {
       $res.=str_repeat(" ", $level)."<$k>\n";
       $res.=$this->toXML($v[$i], $level+1);
       $res.=str_repeat(" ", $level)."</$k>\n";
      }
     } elseif ((Is_Array($v)) && (!IsSet($v[0]))) {
      // hash
      $res.=str_repeat(" ", $level)."<$k>\n".$this->toXML($v, $level+1).str_repeat(" ", $level)."</$k>\n";
     } else {
      // variable
      $res.=str_repeat(" ", $level)."<$k>".$v."</$k>\n";
     }
    }
   }
   return $res;
  }
/**
* Used to create hash-data from XML-based string
*
* 
*
* @param string XML-data
* @access public
*/
  function fromXML($raw, $prev="") {
  // converts from xml to hash
   global $ndx;

   if ($prev=="") {
    $this->ndx=array();
   }

   $raw = preg_replace('/\<\?.+?\?\>\s*/s', '', $raw); //removes xml tag
   $i=0;
   $xml=array();
   $pattern='(\s*?)<([^\?]+?)>(.*?)\1<\/\2>'; // tag-pattern
   if (preg_match_all('/'.$pattern.'/s', $raw, $matches, PREG_PATTERN_ORDER)) {
 
    for($m=0;$m<count($matches[0]);$m++) {
 
     $k=$matches[2][$i];
     $v=$matches[3][$i];

     $res=$this->fromXML($v, $prev.$k.$i);


     if (IsSet($this->ndx["$prev"."$k"])) {
      if ($ndx["$prev"."$k"]==0) {
       $vv=$xml["$k"];
       $xml["$k"]=array();
       $xml["$k"][0]=$vv;
      }
      $this->ndx["$prev"."$k"]++;
      $xml["$k"][$this->ndx["$prev"."$k"]]=$res;
     } else {
      $this->ndx["$prev"."$k"]=0;
      $xml["$k"]=$res;
     }
     $xml["$k"."_list"][$this->ndx["$prev"."$k"]]=$res;
     $i++;
    }


   }
   if ($i==0) {
    return $raw;
   } else {
    return $xml;
   }
  }
// --------------------------------------------------------------------   
 }

// --------------------------------------------------------------------   
// RELATIVE GENERAL FUNCTIONS
// --------------------------------------------------------------------   

 function toXML($hash) {
  $tmp=new xml_data($hash);
  return $tmp->string;
 }
// --------------------------------------------------------------------   

 function fromXML($str) {
  $tmp=new xml_data($str);
  return $tmp->hash;
 }
// --------------------------------------------------------------------   

?>