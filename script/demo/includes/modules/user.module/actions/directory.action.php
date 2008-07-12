<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Actions
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * The user directory
 * @package Demo
 * @subpackage Actions
 *
 */
class UserModule_DirectoryAction extends Krai_Module_Action
{
  /**
   * All the users
   * @var array
   */
  protected $_users = array();

  public function Process()
  {
    $q = self::$DB->FindQuery(array("users as u"));
    $q->fields = array("u.*");
    $q->conditions = "u.directory_list = 'yes'";
    $q->order = "displayname, username";

    $res = self::$DB->Process($q);

    if($res)
    {
      $this->_users = $res;
    }
    else
    {
      throw new Krai_ModuleException("Unable to locate the user records in the database.", Krai_ModuleException::ProcessingError);
    }
  }

  public function Display()
  {
    if(self::IsErrors())
    {
      $this->Render("application.module/views/error.phtml");
    }
    else
    {
      $this->Render("user.module/views/directory.phtml");
    }
  }

  public function HandleError($_ErrorCode, $_ErrorMsg)
  {
    self::Error($_ErrorMsg);
  }
}
