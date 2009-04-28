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
 * Displays user profile pages
 * @package Demo
 * @subpackage Actions
 */
class UserModule_ProfileAction extends Krai_Module_Action
{
  /**
   * The user database record
   * @var Krai_DbObject
   */
  protected $_user = null;

  public function Validate()
  {
    if(is_null(self::$REQUEST->Param("id")) || self::$REQUEST->Param("id") == "")
    {
      throw new Krai_Module_Exception("Username was not provided.", Krai_Module_Exception::ValidationError);
    }
  }

  public function Process()
  {
    $q = self::$DB->SelectQuery(array("users as u"));
    $q->conditions = "u.username = ?";
    $q->parameters = array(self::$REQUEST->Param("id"));
    $q->limit = "1";

    $res = self::$DB->Process($q);

    if($res)
    {
      $this->_user = $res;
    }
    else
    {
      throw new Krai_Module_Exception("Unable to locate the user record in the database.", Krai_Module_Exception::ProcessingError);
    }
  }

  public function Display()
  {
    if(self::IsErrors())
    {
      $this->RedirectTo("user","directory");
    }
    else
    {
      $this->Render("user.module/views/profile.phtml");
    }
  }
}
