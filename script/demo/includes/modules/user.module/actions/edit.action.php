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
 * User profile editing
 * @package Demo
 * @subpackage Actions
 *
 */
class UserModule_EditAction extends Krai_Module_Action
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
      $req_flds = array("email","displayname","directory_list","display_email");

      //Check for required fields having content
      foreach($req_flds as $fld)
      {
        if(!array_key_exists($fld, self::$POST) || empty(self::$POST[$fld]))
        {
          $this->_errorfields[$fld] = "cannot be empty.";
        }
      }

      //Check directory list
      if(!array_key_exists("directory_list", $this->_errorfields) && !in_array(self::$POST["directory_list"], array("yes","no")))
      {
        $this->_errorfields["directory_list"] = "has an invalid value.";
      }

      //Check display email
      if(!array_key_exists("display_email", $this->_errorfields) && !in_array(self::$POST["display_email"], array("yes","no")))
      {
        $this->_errorfields["display_email"] = "has an invalid value.";
      }

      //Check email format
      if(!array_key_exists("email", $this->_errorfields) && !eregi(ApplicationModule::EMAIL_REGEXP, self::$POST["email"]))
      {
        $this->_errorfields["email"] = "does not have a valid format.";
      }

      //Check for e-mail uniqueness
      if(!array_key_exists("email", $this->_errorfields))
      {
        $q = self::$DB->SelectQuery(array("users"));
        $q->conditions = "(email = ? OR new_email = ?) AND user_id != ?";
        $q->parameters = array(self::$POST["email"], self::$POST["email"], $this->_parent->USER->user_id);
        $q->limit = "1";
        $q->fields = array("user_id");

        $res = self::$DB->Process($q);

        if($res)
        {
          $this->_errorfields["email"] = "is already is use. Please choose another.";
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
      //generate a code should it be needed
      $dump =& ApplicationModule::$CHARDUMP;
      $thecode='';
      for($t=0;$t<32;$t++)
      {
        $thecode .= $dump{mt_rand(0,strlen($dump)-1)};
      }

      //Update the user in the database
      $q = self::$DB->UpdateQuery(array("users"));
      $q->conditions = "user_id = ?";
      $q->parameters = array($this->_parent->USER->user_id);
      $q->fields = array(
        "displayname" => self::$POST["displayname"],
        "directory_list" => self::$POST["directory_list"],
        "display_email" => self::$POST["display_email"],
        "new_email" => ($this->_parent->USER->email != self::$POST["email"]) ? self::$POST["email"] : null,
        "confirmation_code" => ($this->_parent->USER->email != self::$POST["email"]) ? $thecode : null
      );

      $res = self::$DB->Process($q);

      if($res->IsSuccessful())
      {
        if($this->_parent->USER->email != self::$POST["email"])
        {
          $mail = Krai_Mail::NewMail();
          $mail->recipients = array(self::$POST["email"]);
          $mail->subject = Krai::GetConfig("SYSTEM_NAME")." E-Mail Change";
          $mail->content =  "Greets.\n\n".
                            "An e-mail change request has been initiated for the ".Krai::GetConfig("SYSTEM_NAME").
                            "To activate the new e-mail, please go to the following URL.\n".
                            "If you did NOT request this e-mail change, please do not visit the link.\n\n".
                            Krai::GetConfig("ROOTURL").self::$ROUTER->UrlFor("user","confirm",array("id" => $this->_parent->USER->user_id,"code" => $thecode,"type"=>"email"), false)."\n\n";
          if(Krai_Mail::Send($mail))
          {
            self::Notice("An e-mail confirmation was sent to the new address for your requested e-mail change.");
          }
          else
          {
            self::Error("The confirmation e-mail failed to be sent.");
          }
        }

        self::$DB->Transaction("commit");
        self::Notice("Preferences saved.");
      }
      else
      {
        self::$DB->Transaction("rollback");
        throw new Krai_Module_Exception("Saving preferences failed. Unable to update user in the database.", Krai_Module_Exception::ProcessingError);
      }

    }
    elseif($this->_doprocess)
    {
      self::$DB->Transaction("rollback");
    }
  }

  public function Display()
  {
    $this->Render("user.module/views/edit.phtml");
  }

}
