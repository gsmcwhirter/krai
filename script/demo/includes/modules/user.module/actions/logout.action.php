<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Actions
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * User logout action
 * @package Demo
 * @subpackage Actions
 */
class UserModule_LogoutAction extends Krai_Module_Action
{
  /**
   * The page referer
   * @var string
   */
  protected $_referrer;

  public function Validate()
  {
    $this->_referrer = $this->_parent->DetermineReferer(null);
  }

  public function Process()
  {
    if(array_key_exists(SETTINGS::COOKIENAME, self::$COOKIES))
    {
      $this->_parent->DestroySession(self::$COOKIES[SETTINGS::COOKIENAME]);

      setcookie(SETTINGS::COOKIENAME, "", time()-3000, Krai::GetConfig("BASEURI") == "" ? "/" : "/".Krai::GetConfig("BASEURI"));
    }

    self::Notice("Successfully logged out.");
  }

  public function Display()
  {
    $this->RedirectTo($this->_referrer);
  }

}
