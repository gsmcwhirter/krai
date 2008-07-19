<?php
/**
 * Krai Framework demo application base module
 *
 * This file contains the application module for the Demo application.
 *
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
 * The initial application module.
 *
 * This class is a descendent of {@link Krai_Module}. It is the base module of
 * the application, in that all other modules in the application should inherit
 * from this one. It has functionality to check logins.
 *
 * @package Demo
 * @subpackage Modules
 *
 */
class ApplicationModule extends Krai_Module
{
  /**
   * Holds the logged-in user instance
   *
   * This variable holds an instance of a logged-in user.
   *
   * @var User
   */
  protected static $_USER = null;

  /**
   * An array of privileges to require for access
   *
   * This variable holds an array to be used in initializing an {@link AccessScheme}
   * for use in privilege checks.
   *
   * @var array
   */
  protected $_RequiresLogin = array();

  /**
   * A dump of valid session key characters
   *
   * This is a dump of characters used for all sorts of things. It is included here
   * so that it does not have to be duplicated everywhere.
   *
   * @var string
   */
  public static $CHARDUMP = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

  /**
   * A regular expression to detect a valid email address
   *
   * This is a regular expression that detects malformed email addresses (at least
   * theoretically).
   *
   * @var string
   */
  const EMAIL_REGEXP = '^[a-zA-Z0-9_\-\.+]+[@+]{1}[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$';

  protected function BeforeFilters()
  {
    parent::BeforeFilters();
    $this->CheckLogin();
    $as = new AccessScheme(array('requires' => $this->_RequiresLogin));

    $this->ValidateAccess($as);
  }

  protected function AfterFilters()
  {
    parent::AfterFilters();
  }

  /**
   * PHP magic overloading.
   *
   * Makes $this->_parent->USER = self::$_USER data accessible in actions
   *
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
   * PHP magic overloading.
   *
   * Prevents the setting of variables.
   *
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
   *
   * This function determines whether or not a user is currently logged in by
   * looking at cookies and checking them against the database.
   *
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
    $q  = self::$DB->SelectQuery(array('sessions as s', 'users as u' => "s.user_id = u.user_id"));
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
   *
   * This function destroys a session in the database.
   *
   * @param string $id The ID of the session to destroy
   * @return boolean
   */
  public function DestroySession($id)
  {
    $q = self::$DB->DeleteQuery('sessions');
    $q->conditions = "session_id = ?";
    $q->parameters = array($id);
    $q->limit = "1";

    $res = self::$DB->Process($q);
    return $res->IsSuccessful();
  }

  /**
   * Validates access according to an AccessScheme
   *
   * This function determines whether or not a user has sufficient privileges to
   * view a page. If not, it usually redirects to the login page, or else returns
   * false if the $justtf parameter is true.
   *
   * @param AccessScheme $as The access scheme to verify against
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
   *
   * This function hashes a password for database storage and for checking inputted
   * passwords against those in the database.
   *
   * @param string $pass
   * @return string
   */
  public function HashPass($pass)
  {
    return "0x".bin2hex(sha1($pass));
  }

}
