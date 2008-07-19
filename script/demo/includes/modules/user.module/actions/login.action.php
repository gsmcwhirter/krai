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
 * User login action
 * @package Demo
 * @subpackage Actions
 */
class UserModule_LoginAction extends Krai_Module_Action
{
  /**
   * Code for local processing
   *
   */
  const ProcessLocal = 3;

  /**
   * Code for no processing
   *
   */
  const NoProcess = 0;

  /**
   * The process type
   * @var integer
   */
  protected $_processtype = self::NoProcess;

  /**
   * Remember the login?
   * @var boolean
   */
  protected $_shouldRemember = null;

  /**
   * The page referer
   * @var string
   */
  protected $_referrer = null;

  public function Validate()
  {
    if($this->_RequestMethod == "POST")
    {
      if(!array_key_exists("action", self::$POST))
      {
        throw new Krai_Module_Exception("Action not found in request.", Krai_Module_Exception::ValidationError);
      }
      elseif(self::$POST["action"] == "login")
      {
        $this->_referrer = $this->_parent->DetermineReferer("post");
	$this->_processtype = self::ProcessLocal;
        if(!array_key_exists("username", self::$POST) || !array_key_exists("password", self::$POST))
        {
          throw new Krai_Module_Exception("Username or password was not present.", Krai_Module_Exception::ValidationError);
        }
	elseif(array_key_exists("remember_me", self::$POST) && !in_array(self::$POST["remember_me"],array("yes","no")))
	{
	  throw new Krai_Module_Exception("Remember option did not have a valid value.", Krai_Module_Exception::ValidationError);
	}
	elseif(array_key_exists("remember_me", self::$POST))
	{
	  $this->_shouldRemember = self::$POST["remember_me"];
	}

        Krai::WriteLog(" RememberMe in Validate:login : ".serialize($this->_shouldRemember), Krai::LOG_DEBUG);
      }
      else
      {
        throw new Krai_Module_Exception("Requested action was not understood.", Krai_Module_Exception::ValidationError);
      }
    }
    else
    {
      $this->_referrer = $this->_parent->DetermineReferer(null);
    }
  }

  public function Process()
  {
    if($this->_processtype == self::ProcessLocal)
    {
      $password = "0x".bin2hex(sha1(self::$POST['password']));

      $q = self::$DB->SelectQuery(array('users as u'));
      $q->fields = array('u.user_id');
      $q->conditions = "username = ? AND password = ?";
      $q->parameters = array(self::$POST["username"], $password);
      $q->limit = "1";

      $res = self::$DB->Process($q);

      if(!$res)
      {
        throw new Krai_Module_Exception("Log-in failed.", Krai_Module_Exception::ProcessingError);
      }

      $this->CreateSession($res->user_id);

      self::Notice("Login was successful.");
      if(array_key_exists("remember", $_SESSION))
      {
        unset($_SESSION["remember"]);
      }
    }
  }

  public function Display()
  {
    if($this->_processtype == self::ProcessLocal && !self::IsErrors())
    {
      Krai::WriteLog(" ReferrerCheck finished Redir : ".serialize((array_key_exists("referrer", $_SESSION)) ? $_SESSION["referrer"] : null), Krai::LOG_DEBUG);
      if(!$this->_referrer)
      {
        $this->_referrer = $this->_parent->DetermineReferer(null);
        //self::Notice("Bug: Referer was not defined. It should have been something.");
      }
      $this->RedirectTo($this->_referrer);
    }
    else
    {
      $this->Render("user.module/views/login.phtml");
    }
  }

  /**
   * Creates a session for the user
   * @param integer $_user_id
   */
  protected function CreateSession($_user_id)
  {
    // user has supplied valid credentials, so log them in
      // generate a 32-character session string to be used to identify the user for subsequent page views
      $charDump='abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      do
      {
        $sessionID='';
        for($t=0;$t<32;$t++)
        {
          $sessionID .= $charDump{mt_rand(0,strlen($charDump)-1)};
        }
        // check to see if 32-character string already exists
	$q = self::$DB->SelectQuery('sessions');
	$q->conditions = "session_id = ?";
	$q->limit = "1";
	$q->parameters = array($sessionID);

	$result = self::$DB->Process($q);

      } while($result);

      //Destroy previous user sessions
      $q = self::$DB->DeleteQuery('sessions');
      $q->conditions = "user_id = ?";
      $q->parameters = array($_user_id);

      self::$DB->Process($q);

      // add a row to the sessions table, containing login info
      $q = self::$DB->InsertQuery('sessions');
      $q->fields = array(
			  "session_id" => $sessionID,
			  "user_id" => $_user_id,
			  "started" => time(),
			  "lastact" => time(),
			  "useragent" => $_SERVER["HTTP_USER_AGENT"],
			  "ipaddr" => $_SERVER["REMOTE_ADDR"]
			);

      self::$DB->Process($q);

      // set cookie
      setcookie(SETTINGS::COOKIENAME, $sessionID, ($this->_shouldRemember == "yes") ? time()+26352000 : 0, Krai::GetConfig("BASEURI") == "" ? "/" : "/".Krai::GetConfig("BASEURI"), SETTINGS::COOKIE_DOMAIN);
  }


}
