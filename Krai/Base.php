<?php
/**
 * Krai base class
 * @package Krai
 * @subpackage Base
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Base/Exception.php"
);

/**
 * Base class of the framework
 * @package Krai
 * @subpackage Base
 */
abstract class Krai_Base
{

  /**
   * Flag for whether or not the framework has been started
   *
   * @var boolean
   */
  private static $_STARTED = false;

  /**
   * Input scrubbing
   *
   * @var Nakor
   */
  private static $_NAKOR_CORE;

  /**
   * Cleaned $_POST copy
   *
   * @var array
   */
  public static $POST = array();

  /**
   * Cleaned $_GET copy
   *
   * @var array
   */
  public static $GET = array();

  /**
   * $_SERVER copy
   *
   * @var array
   */
  public static $SERVER = array();

  /**
   * $_COOKIES copy
   *
   * @var array
   */
  public static $COOKIES = array();

  /**
   * $_SESSION copy
   *
   * @var array
   */
  public static $SESSION = array();

  /**
   * $_ENV copy
   *
   * @var array
   */
  public static $ENV = array();

  /**
   * $_FILES copy
   *
   * @var array
   */
  public static $FILES = array();

  /**
   * $_REQUEST copy
   *
   * @var array
   */
  public static $REQUEST = array();

  /**
   * Holds a merger of self::$GET, self::$POST, and some things the router finds
   *
   * @var array
   */
  public static $PARAMS = array();

  /**
   * Holds the database connections
   *
   * @var mixed
   */
  public static $DB = null;

  /**
   * Holds the default database connection by reference
   *
   * @var Krai_Db
   */
  public static $DB_DEFAULT = null;

  /**
   * Holds the errors and notices
   *
   * @var array
   */
  private static $_MESSAGES = array(
    "errors" => array(),
    "notices" => array()
  );

  /**
   * The router instance
   *
   * @var Krai_Router
   */
  public static $ROUTER;

  /**
   * Startup the Framework
   *
   * @throws Krai_Base_Exception
   */
  public static function RunApplication($_db)
  {
    /*See if this has already been called.*/
    if (self::$_STARTED)
    {
      throw new Krai_Base_Exception("Tried to call Krai_Base::RunApplication() more than once");
    }

    self::$_STARTED = true;
    /* start the input scrubber */
    self::$_NAKOR_CORE = new Nakor();

    /* scrub input etc */
    self::$POST = self::$_NAKOR_CORE->CleanInput("POST");
    self::$GET = self::$_NAKOR_CORE->CleanInput("GET");
    self::$SERVER = $_SERVER;
    self::$COOKIES = $_COOKIE;
    self::$ENV = $_ENV;
    self::$FILES = $_FILES;
    self::$REQUEST = $_REQUEST;
    self::$PARAMS = array_merge(self::$GET, self::$POST);
    self::$ROUTER = Krai_Router::Instance();//Krai::$REQUEST);

    if(count($_db) == 1)
    {
      self::$DB = array_shift($_db);
      self::$DB_DEFAULT =& self::$DB;
    }
    else
    {
      self::$DB = $_db;
      self::$DB_DEFAULT =& self::$DB[array_shift(array_keys($_db))];
    }

    self::ReloadMessages();
    self::$ROUTER->DoRoute(Krai::$REQUEST);
  }

  /**
   * Reloads messages from session if available
   *
   */
  private static function ReloadMessages()
  {
    if(array_key_exists('kvf_messages', $_SESSION))
    {
      if(array_key_exists('errors', $_SESSION['kvf_messages']))
      {
        self::$_MESSAGES["errors"] = $_SESSION['kvf_messages']["errors"];
      }
      if(array_key_exists('notices', $_SESSION['kvf_messages']))
      {
        self::$_MESSAGES["notices"] = $_SESSION['kvf_messages']["notices"];
      }
    }
  }

  /**
   * Saves messages to the session
   *
   */
  public static function SaveMessages()
  {
    $_SESSION['kvf_messages'] = self::$_MESSAGES;
  }

  /**
   * Save an error message
   *
   * @param string $message Message to save
   */
  public static function Error($message)
  {
    self::$_MESSAGES["errors"][] = $message;
  }

  /**
   * Save a notice message
   *
   * @param string $message Message to save
   */
  public static function Notice($message)
  {
    self::$_MESSAGES["notices"][] = $message;
  }

  /**
   * Determine whether there are errors or not
   *
   * @return boolean Whether or not there are errors
   */
  public static function IsErrors()
  {
    return(count(self::$_MESSAGES["errors"]) > 0);
  }

  /**
   * Determine whether there are notices or not
   *
   * @return boolean Whether or not there are notices
   */
  public static function IsNotices()
  {
    return(count(self::$_MESSAGES["notices"]) > 0);
  }

  /**
   * Returns the logged errors
   *
   * @return array
   */
  public static function GetErrors()
  {
    $t = self::$_MESSAGES["errors"];
    self::$_MESSAGES["errors"] = array();
    return $t;
  }

  /**
   * Returns the logged notices
   *
   * @return array
   */
  public static function GetNotices()
  {
    $t = self::$_MESSAGES["notices"];
    self::$_MESSAGES["notices"] = array();
    return $t;
  }
}
