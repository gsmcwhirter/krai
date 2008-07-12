<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Actions
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
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
    if(!array_key_exists("id", self::$PARAMS) || empty(self::$PARAMS["id"]))
    {
      throw new Krai_ModuleException("Username was not provided.", Krai_ModuleException::ValidationError);
    }
  }

  public function Process()
  {
    $q = self::$DB->FindQuery(array("users as u"));
    $q->conditions = "u.username = ?";
    $q->parameters = array(self::$PARAMS["id"]);
    $q->limit = "1";

    $res = self::$DB->Process($q);

    if($res)
    {
      $this->_user = $res;
    }
    else
    {
      throw new Krai_ModuleException("Unable to locate the user record in the database.", Krai_ModuleException::ProcessingError);
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

  public function HandleError($_ErrorCode, $_ErrorMsg)
  {
    self::Error($_ErrorMsg);
  }
}
