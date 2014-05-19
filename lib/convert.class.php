<?php

/**
 * Convert one unit to another
 *
 * @version 1.0
 * @author ldv
 */
class Convert
{
   public function Convert() { }
   
   /**
    * Convert input string from cp1251 codepage to unicode(utf-8)
    * @param string $in Input string
    * @return string
    */
   public static function Cp1251ToUtf8($in) 
   {
      return iconv('windows-1251', 'utf-8', $in);
   }
   
   /**
    * Convert input string from cp1251 codepage to unicode(utf-8)
    * @param string $in Input string
    * @return string
    */
   public static function Utf8ToCp1251($in)
   {
      return iconv('utf-8', 'windows-1251', $in);
   }
   
   /**
    * Convert pressure from mmhg to hpa
    * @param float $pressure Pressure in mmhg
    * @param int $precision Default precision is 2
    * @return float Pressure in hpa or input param
    */
   public static function PressureMmhgToHpa($pressure, $precision = 2)
   {
      if (!is_numeric($vPressure))
         return $vPressure;
      
      $pressure = (float)$pressure;
      
      return round($pressure * 1.33322, $precision);
   }

   /**
    * Convert pressure from hpa to mmhg
    * @param float $pressure Pressure in hpa
    * @param int $precision Default precision is 2
    * @return float Pressure in mmhg or input param
    */
   public static function PressureHpaToMmhg($pressure, $precision = 2)
   {
      if (!is_numeric($vPressure))
         return $vPressure;
      
      $pressure = (float)$pressure;
      
      return round($pressure * 0.75006375541921, $precision);
   }
   
   /**
    * Convert wind direction from azimuth(degree) to wind rose (N,W,S,E)
    * @param decimal $degree Azimuth
    * @return string wind direction in (N,W,S,E)
    */
   public static function WindAzimuthToDirection($degree)
   {
      $windDirection = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW', 'N'];
      
      return $windDirection[round($degree / 22.5)];
   }
   
   /**
    * Convert minutes to seconds
    * @param decimal $minutes Time in minutes
    * @return decimal or null
    */
   public static function TimeMinToSec($minutes)
   {
      if (!is_numeric($minutes))
         return null;
      
      $sec = $minutes * 60;
      
      return $sec;
   }
   
   /**
    * Convert hours to seconds
    * @param decimal $hour Time in hours
    * @return decimal or null
    */
   public static function TimeHourToSec($hour)
   {
      if (!is_numeric($hour))
         return null;
      
      $sec = $hour * 60 * 60;
      
      return $sec;
   }
}
