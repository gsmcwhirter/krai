<?php
/**
 * Krai application skeleton application module
 * @package Demo
 * @subpackage Modules
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$INCLUDES."/lib/access_scheme.class.php",
  Krai::$INCLUDES."/lib/user.class.php"
);

/**
 * The initial application module. Other modules should inherit from this one.
 * @package Demo
 * @subpackage Modules
 *
 */
class ApplicationModule extends Krai_Module
{
  /**
   * Holds the logged-in user instance
   * @var User
   */
  protected static $_USER = null;

  /**
   * An array of privileges to require for access
   * @var array
   */
  protected $_RequiresLogin = array();

  /**
   * A dump of valid session key characters
   * @var string
   */
  public static $CHARDUMP = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

  /**
   * A regular expression to detect a valid email address
   * @var string
   */
  const EMAIL_REGEXP = '^[a-zA-Z0-9_\-\.+]+[@+]{1}[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$';

  /**
   * Filters for before the action execution
   *
   */
  protected function BeforeFilters()
  {
    parent::BeforeFilters();
    $this->CheckLogin();
    $as = new AccessScheme(array('requires' => $this->_RequiresLogin));

    $this->ValidateAccess($as);
  }

  /**
   * Filters for after the action execution
   *
   */
  protected function AfterFilters()
  {
    parent::AfterFilters();
  }

  /**
   * PHP magic overloading. Makes $this->_parent->USER = self::$_USER data accessible in actions
   * @param mixed $n
   * @return mixed
   */
  public function __get($n)
  {
    if($n == "USER")
    {
      return self::$_USER;
    }
    else
    {
      return null;
    }
  }

  /**
   * PHP magic overloading. Prevents the setting of variables.
   * @param mixed $n
   * @param mixed $v
   * @return null
   */
  public function __set($n,$v)
  {
    return null;
  }

  /**
   * Checks whether a user is logged in or not
   * @return boolean
   *
   */
  protected function CheckLogin()
  {
    if(!array_key_exists(SETTINGS::COOKIENAME, $_COOKIE))
    {
      return false;
    }

    if(!$_COOKIE[SETTINGS::COOKIENAME])
    {
      return false;
    }

    // check to see if user has an active session using the session string found in cookie
    $q  = self::$DB->FindQuery(array('sessions as s', 'users as u' => "s.user_id = u.user_id"));
    $q->fields = array('s.session_id','s.started','s.lastact','s.useragent','s.ipaddr','u.*');
    $q->conditions = "s.session_id = ?";
    $q->limit = "1";
    $q->parameters = array($_COOKIE[SETTINGS::COOKIENAME]);

    $res = self::$DB->Process($q);

    if(!$res || !$res->user_id)
    {
      setcookie(SETTINGS::COOKIENAME, "", time() - 3000, Krai::GetConfig("BASEURI") == "" ? "/" : "/".Krai::GetConfig("BASEURI"));
      return false;
    }

    // update the last page view time
    $q = self::$DB->UpdateQuery('sessions');
    $q->conditions = "session_id = ?";
    $q->parameters = array($_COOKIE[SETTINGS::COOKIENAME]);
    $q->limit = "1";
    $q->fields = array('lastact' => time());

    self::$DB->Process($q);

    self::$_USER = new User($res);

    return true;
  }

  /**
   * Destroys a user login session
   * @param string $id The ID of the session to destroy
   * @return boolean
   */
  public function DestroySession($id)
  {
    $q = self::$DB->DeleteQuery('sessions');
    $q->conditions = "session_id = ?";
    $q->parameters = array($id);
    $q->limit = "1";

    return self::$DB->Process($q);
  }

  /**
   * Validates access according to an AccessScheme
   * @param AccessScheme $as
   * @param boolean $justtf Prevents redirecting to a login page if necessary and returns boolean instead
   * @return boolean
   */
  public function ValidateAccess(AccessScheme $as, $justtf = false)
  {
    if(count($as->requires) == 0)
    {
      return true;
    }
    elseif(!is_null(self::$_USER) && self::$_USER->HasPrivilegeFor($as))
    {
      return true;
    }
    elseif(!is_null(self::$_USER))
    {
      //Goto Access Denied
      if($justtf)
      {
        return false;
      }
      else
      {
        self::Error("Access Denied.");
        $this->RedirectTo("page","index");
      }
    }
    else
    {
      //Goto Login
      if($justtf)
      {
        return false;
      }
      else
      {
        $this->RedirectTo("user","login");
      }
    }
  }

  /**
   * Hash a password so it can be checked against the database
   * @param string $pass
   * @return string
   */
  public function HashPass($pass)
  {
    return "0x".bin2hex(sha1($pass));
  }

}
