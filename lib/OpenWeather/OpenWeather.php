<?php

/**
 * Basic implementation of OpenWeatherMap.
 *
 * Get weather data from openweathermap.org
 * 
 * @package OpenWeather
 * @version 1.0
 * @author LDV
 * 
 */
class OpenWeather
{
   /**
    * Get current weather data by City name
    * @param $vCountry  CountrtyCode
    * @param $vCity     CityName
    * @param $vUnits    Unita
    * @return
    */
   protected static function GetJsonWeatherDataByCityName
     ($vCountry,
      $vCity,
      $vUnits)
   {
      $city  = '';
        
      if(isset($vCountry) && isset($vCity))
         $city = $vCity . "," . $vCountry;
      else if (!isset($vCountry) && isset($vCity))
         $city = $vCity;
         
      if (!isset($city)) return null;
         
      if (SETTINGS_SITE_LANGUAGE == 'ru')
      	$query = "http://api.openweathermap.org/data/2.5/weather?q=" . $city . "&lang=ru&units=" . $vUnits;
      else
      	$query = "http://api.openweathermap.org/data/2.5/weather?q=" . $city . "&units=" . $vUnits;
         
      $data = json_decode(file_get_contents($query));
         
      return $data;
   }
      
   /**
    * Get Weather data from openweathermap.org by city id
    * @param $vCityID   CityID
    * @param $vUnits    Unit(metric/imperial)
    * @return
    */
   protected static function GetJsonWeatherDataByCityID
     ($vCityID,
      $vUnits = "metric")
   {
      if (!isset($vCityID)) return null;
      
      $vUnits = OpenWeather::GetUnits($vUnits);
      $query  = "http://api.openweathermap.org/data/2.5/weather?id=" . $vCityID. "&units=" . $vUnits;
      $data   = json_decode(file_get_contents($query));
         
      return $data;
   }
      
   /**
    * Get wind direction name by direction in degree 
    * @param $degree Degree
    * @return
    */
   private static function GetWindDirection
     ($degree)
   {
      $windDirection = ['<#LANG_N#>', '<#LANG_NNE#>', '<#LANG_NE#>', '<#LANG_ENE#>', '<#LANG_E#>', '<#LANG_ESE#>', '<#LANG_SE#>', '<#LANG_SSE#>', '<#LANG_S#>', '<#LANG_SSW#>', '<#LANG_SW#>', '<#LANG_WSW#>', '<#LANG_W#>', '<#LANG_WNW#>', '<#LANG_NW#>', '<#LANG_NNW#>', '<#LANG_N#>'];
         
      return $windDirection[round($degree / 22.5)];
   }
      
   /**
    * Check units for weather. If unit unknown or incorrect then units = metric
    * @param $vUnits
    * @return
    */
   private static function GetUnits
     ($vUnits)
   {
      $units = "metric";
         
      if (!isset($vUnits)) return $units;
         
      if ($vUnits === "imperial")
         return $vUnits;
         
      return $units;
   }
   
   /**
    * Convert Pressure from one system to another. 
    * If error or system not found then function return current pressure.
    * @param $vPressure 
    * @param $vFrom
    * @param $vTo
    * @param $vPrecision
    * @return
    */
   public static function ConvertPressure($vPressure, $vFrom, $vTo, $vPrecision = 2)
   {
      if (empty($vFrom) || empty($vTo) || empty($vPressure))
         return $vPressure;
      
      if (!is_numeric($vPressure))
         return $vPressure;
      
      $vPressure = (float) $vPressure;
      $vFrom     = strtolower($vFrom);
      $vTo       = strtolower($vTo);
      
      if ($vFrom == "hpa" && $vTo == "mmhg")
         return round($vPressure * 0.75006375541921, $vPrecision);
      
      if ($vFrom == "mmhg" && $vTo == "hpa")
         return round($vPressure * 1.33322, $vPrecision);
      
      return $vPressure;
   }
      
   /**
    * Get url to weather's image by icon 
    * @param $vImageIcon
    * @return
    */
   private static function GetWeatherImage($vImageIcon)
   {
      if (!isset($vImageIcon)) return;
         
      $imageUtl = "http://openweathermap.org/img/w/" . $vImageIcon . ".png";
         
      return $imageUtl;
   }
      
   /**
    * Get html weather widget with current wheather for page
       * @param $vCountry CountryCode
       * @param $vCity    CityName
       * @param $vUnits   Units
       * @return
       */
   public static function GetCurrentWeatherWidget
     ($vCountry,
      $vCity,
      $vUnits)
   {
      $vUnits  = OpenWeather::GetUnits($vUnits);
      $weather = OpenWeather::GetJsonWeatherDataByCityName($vCountry,$vCity,$vUnits);
      
      $widget  = "<div class=\"span4\">";
         
      if ($weather->cod == "404")
      {
         $widget .= "<span class=\"label label-danger\">" . $weather->message . "</span>";
      }
      else
      {
         $widget .= "<h3>" . $weather->name . ", " . $weather->sys->country . "</h3>";
         $widget .= "<h2>";
         $widget .= "   <img src=\"" . OpenWeather::GetWeatherImage($weather->weather[0]->icon) . "\" />"; 
         $widget .= round($weather->main->temp, 2);
         $widget .= $vUnits == "metric" ? " °C" : " °F";
         $widget .= "</h2>";
         $widget .= "<p>" . $weather->weather[0]->description . "</p>"; 
         
         $lm_date = date("D M j G:i:s T Y", $weather->dt);
         
         $widget .= "<div id=\"date_m\"><#LANG_GET_AT#> " . $lm_date . "</div>";
         $widget .= "<p>&nbsp;</p>";
         $widget .= "<table class=\"table table-striped table-bordered table-condensed\">";
         $widget .= "   <tbody>";
         $widget .= "      <tr>";
         $widget .= "         <td><#LANG_WIND#></td>";
         $widget .= "         <td><#LANG_SPEED#> " . $weather->wind->speed . "<#LANG_M_S#> <br />" . OpenWeather::GetWindDirection($weather->wind->deg) . "(" . $weather->wind->deg . "°)</td>";
         $widget .= "      </tr>";
         
         $pressure = $vUnits == "metric" ?  OpenWeather::ConvertPressure($weather->main->pressure, "hpa", "mmhg") . "<#LANG_MMHG#>":  $weather->main->pressure . "<#LANG_HPA#>";
         
         $widget .= "     <tr><td><#LANG_PRESSURE#></td><td>" . $pressure . "</td></tr>";
         $widget .= "     <tr><td><#LANG_HUMIDITY#></td><td>".  $weather->main->humidity . "%</td></tr>";
         $widget .= "  </tbody>";
         $widget .= "</table>";
      }
      
      $widget .= "</div>";
         
      return $widget;
   }
   
   /**
    * Get html weather widget with current wheather for page
    * @param $vCityID  CityID
    * @param $vUnits   Units
    * @return
    */
   public static function GetCurrentWeatherWidgetByCityID
     ($vCityID,
      $vUnits)
   {
      $vUnits  = OpenWeather::GetUnits($vUnits);
      $weather = OpenWeather::GetJsonWeatherDataByCityID($vCityID,$vUnits);
      
      $widget  = "<div class=\"span4\">";
      
      if ($weather->cod == "404")
      {
         $widget .= "<span class=\"label label-danger\">" . $weather->message . "</span>";
      }
      else
      {
         $widget .= "<h3>" . $weather->name . ", " . $weather->sys->country . "</h3>";
         $widget .= "<h2>";
         $widget .= "   <img src=\"" . OpenWeather::GetWeatherImage($weather->weather[0]->icon) . "\" />"; 
         $widget .= $weather->main->temp;
         $widget .= $vUnits == "metric" ? " °C" : " °F";
         $widget .= "</h2>";
         $widget .= "<p>" . $weather->weather[0]->description . "</p>";
         
         $lm_date = date("D M j G:i:s T Y", $weather->dt);
         
         $widget .= "<div id=\"date_m\"><#LANG_GET_AT#> " . $lm_date . "</div>";
         $widget .= "<p>&nbsp;</p>";
         $widget .= "<table class=\"table table-striped table-bordered table-condensed\">";
         $widget .= "   <tbody>";
         $widget .= "      <tr>";
         $widget .= "         <td><#LANG_WIND#></td>";
         $widget .= "         <td><#LANG_SPEED#> " . $weather->wind->speed . "<#LANG_M_S#> <br />" . OpenWeather::GetWindDirection($weather->wind->deg) . "(" . $weather->wind->deg . "°)</td>";
         $widget .= "      </tr>";
         
         $pressure = $vUnits == "metric" ?  OpenWeather::ConvertPressure($weather->main->pressure, "hpa", "mmhg") . "<#LANG_MMHG#>":  $weather->main->pressure . "<#LANG_HPA#>";
         
         $widget .= "     <tr><td><#LANG_PRESSURE#></td><td>" . $pressure . "</td></tr>";
         $widget .= "     <tr><td><#LANG_HUMIDITY#></td><td>".  $weather->main->humidity . "%</td></tr>";
         $widget .= "  </tbody>";
         $widget .= "</table>";
      }
      
      $widget .= "</div>";
      
      return $widget;
   }
   
   /**
    * GetWeather data from openweathermap.org by Country and City
    * @param $vCountry
    * @param $vCity
    * @param $vUnits
    * @return
    */
   public static function GetWeather
     ($vCountry,
      $vCity,
      $vUnits)
   {
      $vUnits  = OpenWeather::GetUnits($vUnits);
      $weather = OpenWeather::GetJsonWeatherDataByCityName($vCountry,$vCity,$vUnits);
         
      return $weather;
   }
      
   /**
    * Return weather by City ID
    * @param $vCityID    CityID
    * @param $vUnits     Unit(metric/imperial)
    * @return
    */
   public static function GetWeatherByCityID
     ($vCityID,
      $vUnits)
   {
      $weather = OpenWeather::GetJsonWeatherDataByCityID($vCityID,$vUnits);
      return $weather;
   }
}
