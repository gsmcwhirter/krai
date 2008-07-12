<?php
/**
 * The application configuration file.
 * @package Demo
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008
 */

/**
 * Class representing a logged-in user
 * @package Demo
 * @subpackage Lib
 *
 */
class User
{

  /**
   * A copy of the database record
   * @var Krai_DbObject
   */
  protected $_DBDATA;

  /**
   * A copy of user-initialized parameters
   * @var array
   */
  protected $_MYDATA = array();

  /**
   * A flag for whether the user is a sysop or not
   * @var boolean
   */
  protected $_ISSYSOP = false;

  /**
   * A cache of privilige lookups
   * @var array
   */
  protected static $_PrivLookups = array();

  /**
   * Constructor
   * @param Krai_Db_Object $_data The database record to use
   *
   */
  public function __construct(Krai_Db_Object $_data)
  {
    $this->_DBDATA = $_data;
    $q = Krai_Base::$DB->FindQuery(array("user_roles as ur","roles as r"));
    $q->conditions = "ur.user_id = ? AND ur.role_id = ? AND r.role_id = ur.role_id";
    $q->parameters = array($this->_DBDATA->user_id, 'sysop');
    $q->fields = array("ur.role_id");

    $res = Krai_Base::$DB->Process($q);

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

    $q = Krai_Base::$DB->FindQuery(array("user_roles as ur","roles as r"));
    $q->conditions = "ur.user_id = ? AND ur.role_id IN (".substr($p,0,-2).") AND r.role_id = ur.role_id";
    $q->parameters = array_merge(array($this->_DBDATA->user_id),$as->requires);
    $q->fields = array("ur.role_id");

    $res = Krai_Base::$DB->Process($q);

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

    $q = Krai_Base::$DB->FindQuery(array("user_roles as ur","users as u","roles as r"));
    $q->conditions = "ur.role_id = ? AND ur.user_id = ? AND u.user_id = ur.user_id AND r.role_id = ur.role_id";
    $q->parameters = array($_priv_name, $_user_id);
    $q->fields = array("ur.role_id","ur.user_id");
    $q->limit = "1";

    $res = Krai_Base::$DB->Process($q);

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
 * @package Demo
 * @subpackage Lib
 */
class UserException extends Krai_Base_Exception
{}
