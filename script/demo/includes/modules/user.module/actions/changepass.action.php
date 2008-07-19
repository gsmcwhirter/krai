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
 * Change password action
 * @package Demo
 * @subpackage Actions
 */
class UserModule_ChangepassAction extends Krai_Module_Action
{
  /**
   * Whether or not to do processing
   * @var boolean
   */
  protected $_doprocess = false;

  /**
   * Input error messages
   * @var array
   */
  protected $_errorfields = array();

  public function BeforeFilters()
  {
    $as = new AccessScheme(array('requires' => array("user:active")));

    $this->_parent->ValidateAccess($as);
  }

  public function Validate()
  {
    if($this->_RequestMethod == "POST")
    {
      self::$DB->Transaction("start");
      $this->_doprocess = true;

      //Set required fields
      $req_flds = array("password","password_confirm");

      //Check for required fields having content
      foreach($req_flds as $fld)
      {
        if(!array_key_exists($fld, self::$POST) || empty(self::$POST[$fld]))
        {
          $this->_errorfields[$fld] = "cannot be empty.";
        }
      }

      //Check that the password and password confirm match if required
      if(!array_key_exists("password", $this->_errorfields) && !array_key_exists("password_confirm", $this->_errorfields) && self::$POST["password"] != self::$POST["password_confirm"])
      {
        $this->_errorfields["password_confirm"] = "must match the password entered.";
      }

      if(!array_key_exists("oldpass", self::$POST) ||
          ( array_key_exists("oldpass", self::$POST) &&
            (
             ($this->_parent->USER->password == "" && self::$POST["oldpass"] != "")
             ||
             ($this->_parent->USER->password != "" && $this->_parent->HashPass(self::$POST["oldpass"]) != $this->_parent->USER->password)
            )
          )
        )
      {
        $this->_errorfields["oldpass"] = "is not correct.";
      }

      if(count($this->_errorfields) > 0)
      {
        throw new Krai_Module_Exception("There were problems with your submission.",Krai_Module_Exception::ValidationError);
      }
    }
  }

  public function Process()
  {
    if($this->_doprocess && !self::IsErrors())
    {
      //Update the user in the database
      $q = self::$DB->UpdateQuery(array("users"));
      $q->conditions = "user_id = ?";
      $q->parameters = array($this->_parent->USER->user_id);
      $q->fields = array(
        "password" => $this->_parent->HashPass(self::$POST["password"])
      );

      $res = self::$DB->Process($q);
      if($res->IsSuccessful())
      {
        self::$DB->Transaction("commit");
        self::Notice("Password Changed.");
      }
      else
      {
        self::$DB->Transaction("rollback");
        throw new Krai_Module_Exception("Changing password failed. Unable to update user in the database.", Krai_Module_Exception::ProcessingError);
      }

    }
    elseif($this->_doprocess)
    {
      self::$DB->Transaction("rollback");
    }
  }

  public function Display()
  {
    if($this->_doprocess && !self::IsErrors())
    {
      $this->RedirectTo("user","edit");
    }
    $this->Render("user.module/views/changepass.phtml");
  }

}
