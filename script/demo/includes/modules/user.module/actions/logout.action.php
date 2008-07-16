<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Actions
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
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
    if(array_key_exists(SETTINGS::COOKIENAME, $_COOKIE))
    {
      $this->_parent->DestroySession($_COOKIE[SETTINGS::COOKIENAME]);

      setcookie(SETTINGS::COOKIENAME, "", time()-3000, Krai::GetConfig("BASEURI") == "" ? "/" : "/".Krai::GetConfig("BASEURI"));
    }

    self::Notice("Successfully logged out.");
  }

  public function Display()
  {
    $this->RedirectTo($this->_referrer);
  }

}
