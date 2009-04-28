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
 * Action to recover lost password
 * @package Demo
 * @subpackage Actions
 */
class UserModule_LostpassAction extends Krai_Module_Action
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

  public function Validate()
  {
    if($this->_RequestMethod == "POST")
    {
      self::$DB->Transaction("start");
      $this->_doprocess = true;

      //Set required fields
      $req_flds = array("username");

      //Check for required fields having content
      foreach($req_flds as $fld)
      {
        if(is_null(self::$REQUEST->Post($fld)) || self::$REQUEST->Post($fld) == "")
        {
          $this->_errorfields[$fld] = "cannot be empty.";
        }
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
      //Generate a random password
      $dump =& ApplicationModule::$CHARDUMP;
      $thecode='';
      for($t=0;$t<10;$t++)
      {
        $thecode .= $dump{mt_rand(0,strlen($dump)-1)};
      }

      $q = self::$DB->SelectQuery(array("users"));
      $q->conditions = "username = ?";
      $q->parameters = array(self::$REQUEST->Post("username"));
      $q->fields = array("user_id","email");
      $q->limit = "1";

      $res2 = self::$DB->Process($q);

      if(!$res2)
      {
        self::$DB->Transaction("rollback");
        throw new Krai_Module_Exception("No user with that username was found in the database.", Krai_Module_Exception::ProcessingError);
      }

      //Update the user in the database
      $q = self::$DB->UpdateQuery(array("users"));
      $q->conditions = "user_id = ?";
      $q->parameters = array($res2->user_id);
      $q->fields = array(
        "password" => $this->_parent->HashPass($thecode)
      );

      $res = self::$DB->Process($q);
      if($res->IsSuccessful())
      {
        //Send activation email
        $mail = Krai_Mail::NewMail();
        $mail->recipients = array($res2->email);
        $mail->subject = "Krai Demo App Lost Password";
        $mail->content =  "Greets.\n\n".
                          "A password reset was requested for your account. Below is the new password.\n\n".
                          $thecode."\n\n";
        if(Krai_Mail::Send($mail))
        {
          self::$DB->Transaction("commit");
          self::Notice("A randomly generated password was sent to the email listed for that account.");
        }
        else
        {
          self::$DB->Transaction("rollback");
          throw new Krai_Module_Exception("Password reset failed. E-mail was not sent.", Krai_Module_Exception::ProcessingError);
        }
      }
      else
      {
        self::$DB->Transaction("rollback");
        throw new Krai_Module_Exception("Password reset failed. Unable to update user in the database.", Krai_Module_Exception::ProcessingError);
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
      $this->RedirectTo("user","login");
    }
    else
    {
      $this->Render("user.module/views/lostpass.phtml");
    }
  }

}
