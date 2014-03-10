<?php

/**
 * Basic implementation of OpenWeatherMap.
 *
 * Get weather data from openweathermap.org
 * 
 * @package OpenWeather
 * @version 1.1
 * @author LDV
 * 
 */
class OpenWeather
{
   /**
    * Get current weather data by City name
    * @param string $vCountry  Countrty Code
    * @param string $vCity City Name
    * @param string $vUnits Unit
    * @return array or null
    */
   protected static function GetJsonWeatherDataByCityName($vCountry, $vCity, $vUnits)
   {
      $city  = '';
        
      if(isset($vCountry) && isset($vCity))
         $city = $vCity . "," . $vCountry;
      else if (!isset($vCountry) && isset($vCity))
         $city = $vCity;
         
      if (!isset($city)) return null;
         
      $query = "http://api.openweathermap.org/data/2.5/weather?q=" . $city . "&units=" . $vUnits;
         
      $data = json_decode(file_get_contents($query));
         
      return $data;
   }
      
   /**
    * Get Weather data from openweathermap.org by city id
    * @param int $vCityID  CityID
    * @param string $vUnits  Unit(metric/imperial)
    * @return array or null
    */
   protected static function GetJsonWeatherDataByCityID($vCityID, $vUnits = "metric")
   {
      if (!isset($vCityID)) return null;
      
      $vUnits = OpenWeather::GetUnits($vUnits);
      $query  = "http://api.openweathermap.org/data/2.5/weather?id=" . $vCityID. "&units=" . $vUnits;
      $data   = json_decode(file_get_contents($query));
         
      return $data;
   }
      
   /**
    * Check units for weather. If unit unknown or incorrect then units = metric
    * @param string $vUnits
    * @return string
    */
   private static function GetUnits($vUnits)
   {
      $units = "metric";
         
      if (!isset($vUnits)) return $units;
         
      if ($vUnits === "imperial")
         return $vUnits;
         
      return $units;
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
    * @param string $vCountry  CountryCode
    * @param string $vCity  CityName
    * @param string $vUnits  Units
    * @return html string 
    */
   public static function GetCurrentWeatherWidget($vCountry, $vCity, $vUnits)
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
         $widget .= $weather->main->temp;
         $widget .= $vUnits == "metric" ? " °C" : " °F";
         $widget .= "</h2>";
         $widget .= "<p>" . $weather->weather[0]->description . "</p>";
         
         $lm_date = date("D M j G:i:s T Y", $weather->dt);
         
         $widget .= "<div id=\"date_m\">get at " . $lm_date . "</div>";
         $widget .= "<p>&nbsp;</p>";
         $widget .= "<table class=\"table table-striped table-bordered table-condensed\">";
         $widget .= "   <tbody>";
         $widget .= "      <tr>";
         $widget .= "         <td>Wind</td>";
         $widget .= "         <td>Speed " . $weather->wind->speed . "m/s <br />" . Convert::WindAzimuthToDirection($weather->wind->deg) . "(" . $weather->wind->deg . "°)</td>";
         $widget .= "      </tr>";
         
         $pressure = $vUnits == "metric" ? Convert::PressureHpaToMmhg($weather->main->pressure) . "mmHg":  $weather->main->pressure . "hpa";
         
         $widget .= "     <tr><td>Pressure</td><td>" . $pressure . "</td></tr>";
         $widget .= "     <tr><td>Humidity</td><td>".  $weather->main->humidity . "%</td></tr>";
         $widget .= "  </tbody>";
         $widget .= "</table>";
      }
      
      $widget .= "</div>";
         
      return $widget;
   }
   
   /**
    * Get html weather widget with current wheather for page
    * @param int $vCityID  CityID
    * @param string $vUnits  Units
    * @return html string
    */
   public static function GetCurrentWeatherWidgetByCityID($vCityID, $vUnits)
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
         
         $widget .= "<div id=\"date_m\">get at " . $lm_date . "</div>";
         $widget .= "<p>&nbsp;</p>";
         $widget .= "<table class=\"table table-striped table-bordered table-condensed\">";
         $widget .= "   <tbody>";
         $widget .= "      <tr>";
         $widget .= "         <td>Wind</td>";
         $widget .= "         <td>Speed " . $weather->wind->speed . "m/s <br />" . Convert::WindAzimuthToDirection($weather->wind->deg) . "(" . $weather->wind->deg . "°)</td>";
         $widget .= "      </tr>";
         
         $pressure = $vUnits == "metric" ? Convert::PressureHpaToMmhg($weather->main->pressure) . "mmHg":  $weather->main->pressure . "hpa";
         
         $widget .= "     <tr><td>Pressure</td><td>" . $pressure . "</td></tr>";
         $widget .= "     <tr><td>Humidity</td><td>".  $weather->main->humidity . "%</td></tr>";
         $widget .= "  </tbody>";
         $widget .= "</table>";
      }
      
      $widget .= "</div>";
      
      return $widget;
   }
   
   /**
    * GetWeather data from openweathermap.org by Country and City
    * @param string $vCountry Country code
    * @param string $vCity  City code
    * @param string $vUnits  units
    * @return array
    */
   public static function GetWeather($vCountry, $vCity, $vUnits)
   {
      $vUnits  = OpenWeather::GetUnits($vUnits);
      $weather = OpenWeather::GetJsonWeatherDataByCityName($vCountry,$vCity,$vUnits);
         
      return $weather;
   }
      
   /**
    * Return weather by City ID
    * @param int $vCityID  City ID
    * @param string $vUnits  Unit(metric/imperial)
    * @return
    */
   public static function GetWeatherByCityID($vCityID, $vUnits)
   {
      $weather = OpenWeather::GetJsonWeatherDataByCityID($vCityID,$vUnits);
      return $weather;
   }
}