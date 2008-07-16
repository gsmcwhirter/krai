<?php
/**
 * Krai base class
 *
 * This file contains the class {@link Krai_Base}.
 *
 * @package Krai
 * @subpackage Base
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Base/Exception.php"
);

/**
 * Base class of the framework
 *
 * This class maintains the database connections, cleaned inputs, notice messages
 * and error messages generated and used by the framework.
 *
 * @package Krai
 * @subpackage Base
 */
abstract class Krai_Base
{

  /**
   * Flag for whether or not the framework has been started
   *
   * This variable signifies whether or not the {@link Krai_Base::RunApplication()}
   * method has been called already.
   *
   * @var boolean
   */
  private static $_STARTED = false;

  /**
   * Input scrubbing class instance
   *
   * This variable holds an instance of the input scrubber. It is used in the
   * {@link Krai_Base::RunApplication()} method.
   *
   * @var Nakor
   */
  private static $_NAKOR_CORE;

  /**
   * Cleaned $_POST copy
   *
   * This is a copy of the $_POST data having been run through the input scrubber.
   *
   * @var array
   */
  public static $POST = array();

  /**
   * Cleaned $_GET copy
   *
   * This is a copy of the $_GET data having been run through the input scrubber.
   *
   * @var array
   */
  public static $GET = array();

  /**
   * Holds a merger of self::$GET, self::$POST, and some things the router finds
   *
   * This is a merger of {@link Krai_Base::$GET}, {@link Krai_Base::$POST}, and
   * some other values as may be determined by {@link Krai_Router} in routing a
   * request.
   *
   * @var array
   */
  public static $PARAMS = array();

  /**
   * Holds the database connections
   *
   * This is either an associative array of database connections as defined in the
   * config file and initialized in {@link Krai::Run()}, or a single database if
   * only one was defined.
   *
   * @var mixed
   */
  public static $DB = null;

  /**
   * Holds the default database connection by reference
   *
   * This variable holds the instance of the first defined database connection
   * in {@link Krai_Base::$DB}.
   *
   * @var Krai_Db
   */
  public static $DB_DEFAULT = null;

  /**
   * Holds the errors and notices
   *
   * This array holds the error and notice messages reported by the framework and
   * application.
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
   * This variable holds the instance of the router to be used for this instance
   * of the application.
   *
   * @var Krai_Router
   */
  public static $ROUTER;

  /**
   * Startup the Framework
   *
   * This function cleans the $_GET and $_POST data, initializes the router, saves
   * the database instances, reloads messages saved in the session from things
   * like a redirect, and passes execution to the Router.
   *
   * @throws Krai_Base_Exception
   * @param array $_db An array of database instances
   */
  public static function RunApplication(array $_db)
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
    self::$PARAMS = array_merge(self::$GET, self::$POST);
    self::$ROUTER = Krai_Router::Instance();

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
   * This function reloads notice and error messages that may have been saved in
   * the php session.
   *
   */
  private static function ReloadMessages()
  {
    if(array_key_exists('krai_messages', $_SESSION))
    {
      if(array_key_exists('errors', $_SESSION['krai_messages']))
      {
        self::$_MESSAGES["errors"] = $_SESSION['krai_messages']["errors"];
      }
      if(array_key_exists('notices', $_SESSION['krai_messages']))
      {
        self::$_MESSAGES["notices"] = $_SESSION['krai_messages']["notices"];
      }

      unset($_SESSION['krai_messages']);
    }
  }

  /**
   * Saves messages to the session
   *
   * This function saves notice and error messages to the session for retrieval
   * at the next request execution.
   *
   */
  public static function SaveMessages()
  {
    $_SESSION['krai_messages'] = self::$_MESSAGES;
  }

  /**
   * Save an error message
   *
   * This function records an error message to the message queue
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
   * This function records a notice message to the message queue
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
   * This function determines whether any error messages are in the queue
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
   * This function determines whether any notice messages are in the queue
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
   * This function returns the error messages currently in the queue and clears the
   * queue.
   *
   * @return array The error messages
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
   * This function returns the notice messages currently in the queue and clears
   * the queue.
   *
   * @return array The notice messages
   */
  public static function GetNotices()
  {
    $t = self::$_MESSAGES["notices"];
    self::$_MESSAGES["notices"] = array();
    return $t;
  }
}
