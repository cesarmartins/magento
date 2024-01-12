<?php
/**
 * Mage Plugins, Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mage Plugins Commercial License (MPCL 1.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://mageplugins.net/commercial-license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mageplugins@gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to http://www.mageplugins.net for more information.
 *
 * @category   MP
 * @package    MP_Warehouse
 * @copyright  Copyright (c) 2017-2018 Mage Plugins, Co. and affiliates (https://mageplugins.net/)
 * @license    https://mageplugins.net/commercial-license/ Mage Plugins Commercial License (MPCL 1.0)
 */

if (!defined('_FULL_RECORD_LENGTH'))            define("_FULL_RECORD_LENGTH",50);

if (!class_exists('_geoiprecord', false)) {
    class _geoiprecord {
      var $country_code;
      var $country_code3;
      var $country_name;
      var $region;
      var $city;
      var $postal_code;
      var $latitude;
      var $longitude;
      var $area_code;
      var $dma_code;   # metro and dma code are the same. use metro_code
      var $metro_code;
      var $continent_code;
    }
}

if (!class_exists('_geoipdnsrecord', false)) {
    class _geoipdnsrecord {
      var $country_code;
      var $country_code3;
      var $country_name;
      var $region;
      var $regionname;
      var $city;
      var $postal_code;
      var $latitude;
      var $longitude;
      var $areacode;
      var $dmacode;
      var $isp;
      var $org;
      var $metrocode;
    }
}

if (!function_exists('_getrecordwithdnsservice')) {
    function _getrecordwithdnsservice($str){
      $record = new _geoipdnsrecord;
      $keyvalue = explode(";",$str);
      foreach ($keyvalue as $keyvalue2){
        list($key,$value) = explode("=",$keyvalue2);
        if ($key == "co"){
          $record->country_code = $value;
        }
        if ($key == "ci"){
          $record->city = $value;
        }
        if ($key == "re"){
          $record->region = $value;
        }
        if ($key == "ac"){
          $record->areacode = $value;
        }
        if ($key == "dm" || $key == "me" ){
          $record->dmacode   = $value;
          $record->metrocode = $value;
        }
        if ($key == "is"){
          $record->isp = $value;
        }
        if ($key == "or"){
          $record->org = $value;
        }
        if ($key == "zi"){
          $record->postal_code = $value;
        }
        if ($key == "la"){
          $record->latitude = $value;
        }
        if ($key == "lo"){
          $record->longitude = $value;
        }
      }
      $number = $GLOBALS['_GEOIP_COUNTRY_CODE_TO_NUMBER'][$record->country_code];
      $record->country_code3 = $GLOBALS['_GEOIP_COUNTRY_CODES3'][$number];
      $record->country_name = $GLOBALS['_GEOIP_COUNTRY_NAMES'][$number];
      if ($record->region != "") {
        if (($record->country_code == "US") || ($record->country_code == "CA")){
          $record->regionname = $GLOBALS['ISO'][$record->country_code][$record->region];
        } else {
          $record->regionname = $GLOBALS['FIPS'][$record->country_code][$record->region];
        }
      }
      return $record;
    }
}

if (!function_exists('__get_record_v6')) {
    function __get_record_v6($gi,$ipnum){
      $seek_country = __geoip_seek_country_v6($gi,$ipnum);
      if ($seek_country == $gi->databaseSegments) {
        return NULL;
      }
      return __common_get_record($gi, $seek_country);
    }
}

if (!function_exists('__common_get_record')) {
    function __common_get_record($gi, $seek_country){
      // workaround php's broken substr, strpos, etc handling with
      // mbstring.func_overload and mbstring.internal_encoding
      $enc = mb_internal_encoding();
      mb_internal_encoding('ISO-8859-1'); 
    
      $record_pointer = $seek_country + (2 * $gi->record_length - 1) * $gi->databaseSegments;
      
      if ($gi->flags & _GEOIP_MEMORY_CACHE) {
        $record_buf = substr($gi->memory_buffer,$record_pointer,_FULL_RECORD_LENGTH);
      } elseif ($gi->flags & _GEOIP_SHARED_MEMORY){
        $record_buf = @shmop_read($gi->shmid,$record_pointer,_FULL_RECORD_LENGTH);
      } else {
        fseek($gi->filehandle, $record_pointer, SEEK_SET);
        $record_buf = fread($gi->filehandle,_FULL_RECORD_LENGTH);
      }
      $record = new _geoiprecord;
      $record_buf_pos = 0;
      $char = ord(substr($record_buf,$record_buf_pos,1));
        $record->country_code = $gi->_GEOIP_COUNTRY_CODES[$char];
        $record->country_code3 = $gi->_GEOIP_COUNTRY_CODES3[$char];
        $record->country_name = $gi->_GEOIP_COUNTRY_NAMES[$char];
      $record->continent_code = $gi->_GEOIP_CONTINENT_CODES[$char];
      $record_buf_pos++;
      $str_length = 0;
        // Get region
      $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
      while ($char != 0){
        $str_length++;
        $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
      }
      if ($str_length > 0){
        $record->region = substr($record_buf,$record_buf_pos,$str_length);
      }
      $record_buf_pos += $str_length + 1;
      $str_length = 0;
        // Get city
      $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
      while ($char != 0){
        $str_length++;
        $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
      }
      if ($str_length > 0){
        $record->city = substr($record_buf,$record_buf_pos,$str_length);
      }
      $record_buf_pos += $str_length + 1;
      $str_length = 0;
        // Get postal code
      $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
      while ($char != 0){
        $str_length++;
        $char = ord(substr($record_buf,$record_buf_pos+$str_length,1));
      }
      if ($str_length > 0){
        $record->postal_code = substr($record_buf,$record_buf_pos,$str_length);
      }
      $record_buf_pos += $str_length + 1;
      $str_length = 0;
        // Get latitude and longitude
      $latitude = 0;
      $longitude = 0;
      for ($j = 0;$j < 3; ++$j){
        $char = ord(substr($record_buf,$record_buf_pos++,1));
        $latitude += ($char << ($j * 8));
      }
      $record->latitude = ($latitude/10000) - 180;
      for ($j = 0;$j < 3; ++$j){
        $char = ord(substr($record_buf,$record_buf_pos++,1));
        $longitude += ($char << ($j * 8));
      }
      $record->longitude = ($longitude/10000) - 180;
      if (_GEOIP_CITY_EDITION_REV1 == $gi->databaseType){
        $metroarea_combo = 0;
        if ($record->country_code == "US"){
          for ($j = 0;$j < 3;++$j){
            $char = ord(substr($record_buf,$record_buf_pos++,1));
            $metroarea_combo += ($char << ($j * 8));
          }
          $record->metro_code = $record->dma_code = floor($metroarea_combo/1000);
          $record->area_code = $metroarea_combo%1000;
        }
      }
      mb_internal_encoding($enc);
      return $record;
    }
}

if (!function_exists('_GeoIP_record_by_addr_v6')) {
    function _GeoIP_record_by_addr_v6 ($gi,$addr){
      if ($addr == NULL){
         return 0;
      }
      $ipnum = inet_pton($addr);
      return __get_record_v6($gi, $ipnum);
    }
}

if (!function_exists('__get_record')) {
    function __get_record($gi,$ipnum){
      $seek_country = __geoip_seek_country($gi,$ipnum);
      if ($seek_country == $gi->databaseSegments) {
        return NULL;
      }
      return __common_get_record($gi, $seek_country);
    }
}

if (!function_exists('_GeoIP_record_by_addr')) {
    function _GeoIP_record_by_addr ($gi,$addr){
      if ($addr == NULL){
         return 0;
      }
      $ipnum = ip2long($addr);
      return __get_record($gi, $ipnum);
    }
}

?>
