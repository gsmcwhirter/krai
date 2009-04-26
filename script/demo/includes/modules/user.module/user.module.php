<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Modules
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
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

    if($source == "post" && !is_null(self::$REQUEST->Post("referrer")))
    {
      return urldecode(self::$REQUEST->Post("referrer"));
    }
    elseif($source == "session" && array_key_exists("referrer", $_SESSION))
    {
      return urldecode($_SESSION["referrer"]);
    }
    elseif($source != "post" && $source != "session")
    {
		if(preg_match("#".Krai::GetConfig("DOMAIN")."$#", parse_url(self::$REQUEST->Referer(),PHP_URL_HOST)) && !preg_match("#user/login#", self::$REQUEST->Referer()))
		{
			return self::$REQUEST->Referer();
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
