<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Modules
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * User functionality module
 * @package Demo
 * @subpackage Modules
 */
class UserModule extends ApplicationModule
{

  /**
   * Determines the page referrer within the domain (or defaults to within the domain)
   * @param string $source The source to look at ("post", "session" or null)
   * @return string
   */
  public function DetermineReferer($source)
  {

    Krai::WriteLog(" Session: ".serialize($_SESSION), Krai::LOG_DEBUG);

    if($source == "post" && array_key_exists("referrer",self::$POST))
    {
      return urldecode(self::$POST["referrer"]);
    }
    elseif($source == "session" && array_key_exists("referrer", $_SESSION))
    {
      return urldecode($_SESSION["referrer"]);
    }
    elseif($source != "post" && $source != "session")
    {
      $parts = ((array_key_exists("HTTP_REFERER", self::$SERVER) && self::$SERVER["HTTP_REFERER"] != "")) ? parse_url(self::$SERVER["HTTP_REFERER"], PHP_URL_HOST) : null;
      if(!is_null($parts) && preg_match("#".Krai::GetConfig("DOMAIN")."$#", $parts) && !preg_match("#user/login#",self::$SERVER["HTTP_REFERER"]))
      {
        return self::$SERVER["HTTP_REFERER"];
      }
      else
      {
        return self::$ROUTER->UrlFor("page","index",array(), false);
      }
    }

  }

  /**
   * Clears the referer out of the session
   *
   */
  public function ClearReferer()
  {
    if(array_key_exists("referrer", $_SESSION))
    {
      unset($_SESSION["referrer"]);
    }

    Krai::WriteLog(" Cleared Referrer", Krai::LOG_DEBUG);
  }

  /**
   * Sets the referer into the session
   * @param string $ref The referer string
   */
  public function SetReferer($ref)
  {
    $_SESSION["referrer"] = urlencode($ref);
  }

}
