<?php
/**
 * A user class for the application.
 *
 * This file contains a class representing a logged-in user and also providing
 * some static functionality
 *
 * @package Demo
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Class representing a logged-in user
 *
 * This class represents a logged-in user. Additionally, it implements some static
 * functionality for privilege checking.
 *
 * @package Demo
 * @subpackage Lib
 *
 */
class User
{

  /**
   * A copy of the database record
   *
   * This variable holds a copy of the database information for the user
   *
   * @var Krai_Db_Object
   */
  protected $_DBDATA;

  /**
   * A copy of user-initialized parameters
   *
   * This variable holds the values of properties that the user initialized on
   * the object
   *
   * @var array
   */
  protected $_MYDATA = array();

  /**
   * A flag for whether the user is a sysop or not
   *
   * This is a cache of the check for whether or not the user has sysop privileges
   *
   * @var boolean
   */
  protected $_ISSYSOP = false;

  /**
   * A cache of privilige lookups
   *
   * This is a cache of privilege lookups, so the database doesn't have to be called
   * for every lookup request necessarily.
   *
   * @var array
   */
  protected static $_PrivLookups = array();

  /**
   * Constructor
   *
   * This function initializes the user object. It saves the database record to
   * {@link User::$_DBDATA} and looks up whether or not the user is a sysop.
   *
   * @param Krai_Db_Object $_data The database record to use
   *
   */
  public function __construct(Krai_Db_Object $_data)
  {
    $this->_DBDATA = $_data;
    $q = Krai::$DB->FindQuery(array("user_roles as ur","roles as r"));
    $q->conditions = "ur.user_id = ? AND ur.role_id = ? AND r.role_id = ur.role_id";
    $q->parameters = array($this->_DBDATA->user_id, 'sysop');
    $q->fields = array("ur.role_id");

    $res = Krai::$DB->Process($q);

    if($res)
    {
      $this->_ISSYSOP = true;
    }
  }

  /**
   * PHP magic overloading. Tries to get the value from the _DBDATA and then the _MYDATA.
   * @param mixed $k The name of the value to get
   * @return mixed The value of the name, or null if not found
   */
  public function __get($k)
  {
    return $this->_DBDATA->$k ? $this->_DBDATA->$k : (array_key_exists($k, $this->_MYDATA) ? $this->_MYDATA[$k] : null);
  }

  /**
   * PHP magic overloading. Tries to set the value to _MYDATA.
   * @throws UserException When the key is the name of a field in the database record
   * @param mixed $k The name of the value to set
   * @param mixed $v The value to set
   */
  public function __set($k, $v)
  {
    if($this->_DBDATA->$k)
    {
      throw new UserException("Trying to overwrite a database value");
    }
    else
    {
      $this->_MYDATA[$k] = $v;
    }
  }

  /**
   * Does a privilige lookup for an access scheme
   *
   * This function performs a privilege lookup based on an {@link AccessScheme}.
   * It returns true if the user has all the required privileges, and false otherwise.
   *
   *
   * @param AccessScheme $as
   * @return boolean
   */
  public function HasPrivilegeFor(AccessScheme $as)
  {
    if($this->_ISSYSOP)
    {
      return true;
    }

    $c = count($as->requires);
    $p = "";
    for($i = 0; $i < $c; $i++)
    {
      $p .= "?, ";
    }

    $q = Krai::$DB->FindQuery(array("user_roles as ur","roles as r"));
    $q->conditions = "ur.user_id = ? AND ur.role_id IN (".substr($p,0,-2).") AND r.role_id = ur.role_id";
    $q->parameters = array_merge(array($this->_DBDATA->user_id),$as->requires);
    $q->fields = array("ur.role_id");

    $res = Krai::$DB->Process($q);

    $res2 = array();
    foreach($res as $r)
    {
      $res2[] = $r->role_id;
    }

    $res2 = array_unique($res2);
    sort($res2);
    $asr = array_unique($as->requires);
    sort($asr);

    if($res2 !== $asr)
    {
      return false;
    }
    else
    {
      return true;
    }
  }

  /**
   * A static privilige lookup
   *
   * This is a static privilege lookup for a user id and a privilege name. It does
   * not require the use of an {@link AccessScheme} instance.
   *
   * @param integer $_user_id The id of the user
   * @param string $_priv_name The name of the privilege
   * @return boolean
   */
  public static function HasPrivilege($_user_id, $_priv_name)
  {
    if(array_key_exists(intval($_user_id), self::$_PrivLookups) && array_key_exists($_priv_name, self::$_PrivLookups))
    {
      return self::$_PrivLookups[$_user_id][$_priv_name];
    }

    if(!array_key_exists(intval($_user_id), self::$_PrivLookups))
    {
      self::$_PrivLookups[intval($_user_id)] = array();
    }

    $q = Krai::$DB->FindQuery(array("user_roles as ur","users as u","roles as r"));
    $q->conditions = "ur.role_id = ? AND ur.user_id = ? AND u.user_id = ur.user_id AND r.role_id = ur.role_id";
    $q->parameters = array($_priv_name, $_user_id);
    $q->fields = array("ur.role_id","ur.user_id");
    $q->limit = "1";

    $res = Krai::$DB->Process($q);

    if($res)
    {
      self::$_PrivLookups[intval($_user_id)][$_priv_name] = true;
      return true;
    }
    else
    {
      self::$_PrivLookups[intval($_user_id)][$_priv_name] = false;
      return false;
    }
  }

}

/**
 * Represents an exception in the User class
 *
 * This is an exception class thrown by {@link User} functions.
 *
 * @package Demo
 * @subpackage Lib
 */
class UserException extends Krai_Exception
{}
