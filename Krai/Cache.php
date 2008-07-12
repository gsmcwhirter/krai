<?php
/**
 * Krai caching class
 * @package Krai
 * @subpackage Cache
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Cache/Exception.php"
);

/**
 * Caching class
 * @package Krai
 * @subpackage Cache
 */
abstract class Krai_Cache
{

  /**
   * Tries to get a saved cache
   *
   * @param string $_cachekey
   * @throws Krai_Cache_Exception
   * @return mixed
   */
  public static function GetCache($_cachekey)
  {
    if(!is_string($_cachekey))
    {
      throw new Krai_Cache_Exception("Cache key must be a string.");
    }

    $cconf = Krai::GetConfig("CONFIG_CACHE");
    if($ret = @unserialize(file_get_contents($cconf["DIR"]."/".base64_encode($_cachekey).".kvfcache")))
    {
      if(isset($ret["timeout"]) && isset($ret["data"]) && time() < $ret["timeout"])
      {
        return $ret["data"];
      }
      else
      {
        self::ExpireCache($_cachekey);
        return null;
      }
    }
    else
    {
      return null;
    }
  }

  /**
   * Tries to save a cache
   *
   * @param string $_cachekey Key to cache under
   * @param mixed $_cachevalue Value to cache
   * @param integer $_cachetimeout Number of seconds to keep the cache
   * @return boolean
   */
  public static function SaveCache($_cachekey, $_cachevalue, $_cachetimeout = null)
  {
    $cconf = Krai::GetConfig("CONFIG_CACHE");

    if(!is_string($_cachekey))
    {
      throw new Krai_Cache_Exception("Cache key must be a string.");
    }

    if(is_null($_cachetimeout))
    {
      $_cachetimeout = $cconf["TIMEOUT"];
    }

    if(@file_put_contents($cconf["DIR"]."/".base64_encode($_cachekey).".kvfcache", serialize(array("timeout" => time() + $_cachetimeout, "data" => $_cachevalue))))
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Tries to expire an existing cache
   *
   * @param string $_cachekey
   * @return boolean
   */
  public static function ExpireCache($_cachekey)
  {
    if(!is_string($_cachekey))
    {
      throw new Krai_Cache_Exception("Cache key must be a string.");
    }

    $cconf = Krai::GetConfig("CONFIG_CACHE");
    $fn = $cconf["DIR"]."/".base64_encode($_cachekey).".kvfcache";
    if(file_exists($fn))
    {
      return unlink($fn);
    }
    else
    {
      return true;
    }
  }
}
