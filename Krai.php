<?php
/**
 * This file is the initializer for the rest of the framework.
 *
 * This file holds the base class, {@link Krai}, which is responsible for
 * configuration and initialization of the framework.
 *
 * @package Krai
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

define_syslog_variables();

/**
 * This is the framework configuration and initialization functionality class.
 *
 * This class is responsible for configuring and initializing the rest of the
 * framework. Applications should include this file in the first script.
 * Then the application should call {@link Krai::Setup()} with the name of the
 * config file to use, followed by {@link Krai::Run()}.
 *
 *  @package Krai
 */
final class Krai
{
  /**#@+
   * Logger level constant
   *
   * These are log level constants for use with {@link Krai_Log}.
   *
   */
  const LOG_EMERG = LOG_EMERG;
  const LOG_ALERT = LOG_ALERT;
  const LOG_CRITICAL = LOG_CRIT;
  const LOG_ERROR = LOG_ERR;
  const LOG_WARNING = LOG_WARNING;
  const LOG_NOTICE = LOG_NOTICE;
  const LOG_INFO = LOG_INFO;
  const LOG_DEBUG = LOG_DEBUG;
  /**#@-*/

  /**
   * Application filesystem root directory variable
   *
   * This variable holds the value of the path to the application root directory.
   * It can be in the config file which is passed to {@link Krai::Setup()}.
   * If it is not set, it defaults to the directory holding {@link Krai.php}.
   *
   * @var string
   */
  public static $APPDIR;

  /**
   * Framwork filesystem directory variable
   *
   * This variable holds the value of the path to the framework root directory.
   * The value defaults to to the directory Krai within the directory holding
   * {@link Krai.php}. There is currently not a way to alter this, but there is
   * also no expectation that it should need to be altered. The value has no
   * trailing slash.
   *
   * @var string
   */
  public static $FRAMEWORK;

  /**
   * Application includes directory variable
   *
   * This variable holds the value of the path to the application includes directory.
   * It gets set to {@link Krai::$APPDIR}/includes. The value has no trailing slash.
   *
   * @var string
   */
  public static $INCLUDES;

  /**
   * Application modules directory variable
   *
   * This variable holds the value of the path to the application modules directory.
   * It gets set to {@link Krai::$INCLUDES}/modules. The value has no trailing slash.
   *
   * @var string
   */
  public static $MODULES;

  /**
   * Application layouts directory variable
   *
   * This variable holds the value of the path to the application layouts directory.
   * It gets set to {@link Krai::$INCLUDES}/layouts. The value has no trailing slash.
   *
   * @var string
   */
  public static $LAYOUTS;

  /**
   * Starting micro timestamp
   *
   * This is the {@link PHP_MANUAL#microtime} of more-or-less the start of the
   * framework execution.
   *
   * @var float
   */
  public static $STARTTIME;

  /**
   * Inflector instance
   *
   * This variable holds an instance of {@link Krai_Lib_Inflector}, which can be used
   * to convert from WordsWithUnderscores |-> words_with_underscores and back,
   * among other functionality.
   *
   * @var Krai_Lib_Inflector
   */
  public static $INFLECTOR = null;

  /**
   * The page requested
   *
   * This variable holds the string of the page that was requested. It is determined
   * either by passing a string to {@link Krai::Run()}, or else automatically
   * set by the call to {@link Krai::DetermineRequest()} from within {@link Krai::Run()}.
   *
   * @var string
   */
  public static $REQUEST;

  /**
   * Flag for whether a MIME type has been set or not
   *
   * This is a flag representing whether or not a Content-Type header has been
   * sent yet in the script. It is manipulated through {@link Krai::SetMime()}.
   *
   * @var boolean
   */
  private static $_MIMESET = false;

  /**
   * Framework logging flag
   *
   * This is a flag representing whether or not application-level logging has been
   * enabled and initialized in the framework. It is used by {@link Krai::WriteLog()}
   * to determine whether to try writing the log message or whether to store it for
   * writing when the logger is initialized.
   *
   * @var boolean
   */
  private static $_LOGGING;

  /**
   * Framework log message cache
   *
   * This is an array in which to hold log messages that are attempted to be sent
   * to the logger for writing when the logger is not ready.
   *
   * @var array
   */
  private static $_BACKLOGS = array();

  /**
   * Whether or not the application was run
   *
   * This is a flag representing whether or not {@link Krai::Run()} has yet been
   * called. This prevents things from being run twice.
   *
   * @var boolean
   */
  private static $_STARTED = false;

  /**
   * Whether or not the application was set up
   *
   * This is a flag representing whether or not {@link Krai::Setup()} has yet been
   * called. This prevents things from being set up twice.
   *
   * @var boolean
   *
   */
  private static $_SETUP = false;

  /**
   * Holds the application configuration
   *
   * This is an array holding the data that was gleaned from a YAML file when
   * {@link Krai::Setup()} was run.
   *
   * @var array
   *
   */
  private static $_CONFIG = array();

  /**
   * Gets information from the configuration array {@link Krai::$_CONFIG}
   *
   * @param string $_key The name of the configuration key to get
   * @return mixed The value of the key
   * @throws Exception When the key is not found
   */
  public static function GetConfig($_key)
  {
    if(array_key_exists($_key, self::$_CONFIG))
    {
      return self::$_CONFIG[$_key];
    }
    else
    {
      throw new Exception("Key not present in configuration.");
    }
  }

  /**
   * This is the configuration function for the framework.
   *
   * This function sets up the framework in preparation of being run.
   *
   * @param string $_conf_file The path to the configuration file.
   *
   */
  public static function Setup($_conf_file)
  {
    if(!self::$_SETUP)
    {
      self::$STARTTIME = microtime(true);

      self::$FRAMEWORK = realpath(dirname(__FILE__)."/Krai");

      require_once self::$FRAMEWORK."/Lib.php";

      if(!file_exists($_conf_file))
      {
        throw new Exception("Configuration file does not exist.");
      }

      self::$_CONFIG = Spyc::YAMLLoad($_conf_file);


      //Output buffering setup
      try
      {
        if(self::GetConfig("DISABLE_OB"))
        {
          $startob = false;
          //Don't start output buffering
        }
        else
        {
          $startob = true;
        }
      }
      catch(Exception $e)
      {
        $startob = true;
      }

      if($startob)
      {
        try
        {
          if(self::GetConfig("USE_OB_GZHANDLER"))
          {
            ob_start("ob_gzhandler");
          }
          else
          {
            ob_start();
          }
        }
        catch(Exception $e)
        {
          ob_start("ob_gzhandler");
        }
      }

      //Session setup
      try
      {
        if(!self::GetConfig("DISABLE_SESSION"))
        {
          session_start();
        }
      }
      catch(Exception $e)
      {
        session_start();
      }

      //Timezone setup
      try
      {
        date_default_timezone_set(self::GetConfig("DEFAULT_TIMEZONE"));
      }
      catch(Exception $e)
      {
        date_default_timezone_set("America/New_York");
      }

      //Appdir setup
      try
      {
        self::$APPDIR = realpath(self::GetConfig("APPDIR"));
      }
      catch(Exception $e)
      {
        self::$APPDIR = realpath(dirname(__FILE__));
      }

      self::$INCLUDES = self::$APPDIR."/includes";
      self::$MODULES = self::$INCLUDES."/modules";
      self::$LAYOUTS = self::$INCLUDES."/layouts";

      self::$_SETUP = true;
    }
  }

  /**
   * Makes everything start up and work
   * @param string $_uri The request to process
   *
   */
  public static function Run($_uri = null)
  {
    try
    {
      if(!self::$_SETUP)
      {
        throw new Exception("You must run Krai::Setup() before Krai::Run");
      }

      if(self::$_STARTED)
      {
        throw new Exception("Application was already started.");
      }

      self::$_STARTED = true;

      if(is_null($_uri))
      {
        $_uri = self::DetermineRequest();
      }

      self::$REQUEST = urldecode($_uri);

      self::Uses(
        self::$FRAMEWORK."/Base.php",
        self::$FRAMEWORK."/Struct.php"
      );


      if(self::GetConfig("USE_LOG"))
      {
        self::Uses(self::$FRAMEWORK."/Log.php");

        $lconf = self::GetConfig("CONFIG_LOG");

        $LOGINFO = new Krai_Struct_Loginfo();
        $LOGINFO->types = $lconf["TYPES"];
        $LOGINFO->configs = $lconf["CONFS"];
        $LOGINFO->default = $lconf["DEFAULT"];

        Krai_Log::Start($LOGINFO);
        self::$_LOGGING = true;
      }
      else
      {
        self::$_LOGGING = false;
      }

      self::Uses(
        self::$FRAMEWORK."/Router.php",
        self::$FRAMEWORK."/Module.php",
        self::$FRAMEWORK."/Markup.php"
      );

      if(self::GetConfig("USE_CACHE"))
      {
        $cconf = self::GetConfig("CONFIG_CACHE");
        self::Uses(self::$FRAMEWORK."/Cache.php");
      }

      if(self::GetConfig("USE_DB"))
      {
        $dconf = self::GetConfig("CONFIG_DB");

        self::Uses(self::$FRAMEWORK."/Db.php");

        $DB = array();
        foreach($dconf["DATA"] as $_dbn => $_dbd)
        {
          $_dbclass = Krai_Db::ClassLookup($_dbd["_type"]);
          $DB[$_dbn] = new $_dbclass($_dbd);
        }
      }
      else
      {
        $DB = array();
      }

      if(self::GetConfig("USE_MAIL"))
      {
        $mconf = self::GetConfig("CONFIG_MAIL");

        self::Uses(self::$FRAMEWORK."/Mail.php");

        Krai_Mail::Configure(
          $mconf["SEND_MAIL"],
          $mconf["FROM_ADDR"],
          $mconf["FROM_NAME"]
        );

      }

      if(is_null(self::$REQUEST))
      {
        self::$REQUEST = "/";
      }

      if(!self::$INFLECTOR instanceOf Krai_Lib_Inflector)
      {
        self::$INFLECTOR = new Krai_Lib_Inflector();
      }

      Krai_Base::RunApplication($DB);
    }
    catch(Krai_Module_Exception_Mdone $e)
    {
      self::EndRun();
    }
    catch(Exception $e)
    {
      include self::$FRAMEWORK."/Exception.phtml";
      exit(0);
    }

    self::EndRun();
  }

  /**
   * Sets the mime-type header for the response
   * @param string $type The mime-type to set
   * @param boolean $force Flag to force a reset if the type had previously been set
   *
   */
  public static function SetMime($type, $force = false)
  {

    if(self::$_MIMESET && !$force)
    {
      return false;
    }
    else
    {
      header("Content-type: ".$type);
      self::$_MIMESET = true;
    }
  }

  /**
   * Ends the application run after cleaning up
   *
   */
  public static function EndRun()
  {
    Krai_Base::SaveMessages();
    if(!self::$_MIMESET)
    {
      self::SetMime("text/html");
    }

    if(self::$_LOGGING)
    {
      Krai_Log::Close();
    }

    session_commit();
    ob_end_flush();
    exit(0);
  }

  /**
   * Determines the request to be used from server variables.  Called in the application's default.php.
   * @return string The actual request to parse.
   */
  private static function DetermineRequest()
  {
    //get rid of the query string for these purposes
    $the_request = preg_replace("#\?.*$#", "", $_SERVER['REQUEST_URI']);

    /*
    //get rid of the query string on the script name, as well
    $the_handling_script = preg_replace("#\?.*$#", "", $_SERVER['SCRIPT_NAME']);
    //drop the default.php from the handling script thing
    $the_handling_script_path = str_replace("/default.php","",$the_handling_script);
    $the_request_actual = preg_replace("#$the_handling_script_path/#", "/", $the_request);
    return ($the_request_actual == "/" || $the_request_actual == "") ? "/index" : $the_request_actual;
    */

    $the_request_actual = preg_replace(array("#^/*#","#^".self::GetConfig("BASEURI")."#"),array("",""),$the_request);
    return ($the_request_actual == "") ? "/" : $the_request_actual;
  }

  /**
   * A wrapper for including files and logging such. Uses func_get_args() for variable argument number.
   * @return boolean
   * @throws Exception
   */
  public static function Uses()
  {
    $args = func_get_args();
    if(count($args) > 0 && is_bool($args[0]))
    {
      $autoload = array_shift($args);
    }
    else
    {
      $autoload = false;
    }

    foreach($args as $filename)
    {
      if(!(@include_once $filename))
      {
        Krai::WriteLog("Failed loading file: ".$filename);

        throw new Exception(($autoload ? "" : "Auto" )."Load files failed for ".$filename);
        return false;
      }

      Krai::WriteLog("Loading file: ".$filename, Krai::LOG_DEBUG);
    }

    return true;
  }

  /**
   * A function to implode an associative array preserving keys
   * @param string $majorglue The glue for between the array entries
   * @param string $minorglue The glue for between key => value pairs
   * @param array $array The array to implode
   * @return string
   */
  public static function AssocImplode($majorglue, $minorglue, array $array)
  {
    $ret = array();
    foreach($array as $k => $v)
    {
      $ret[] = $k.$minorglue.$v;
    }
    return implode($majorglue, $ret);
  }

  /**
   * Provides an interface to the loghandler, whatever that might be.
   * @param string $message
   * @param integer $level
   * @param array $logs
   * @param string $cat
   * @param array $forces
   *
   */
  public static function WriteLog($message, $level = Krai::LOG_INFO, array $logs = array(), $cat = null, array $forces = array())
  {
    if (self::$_LOGGING)
    {
      Krai_Log::Write($message,$level,$logs,$cat,$forces);
    }
    else
    {
      self::$_BACKLOGS[] = array($message, $level, $logs, $cat, $forces);
    }
  }

  /**
   * Writes the back logs to the logger
   *
   */
  private static function WriteBackLogs()
  {
    if (self::$_LOGGING)
    {
      $keys = array_keys(self::$_BACKLOGS);
      foreach($keys as $logkey)
      {
        $log = self::$_BACKLOGS[$logkey];
        self::WriteLog($log[0], $log[1], $log[2], $log[3], $log[4]);
        unset($log);
        unset(self::$_BACKLOGS[$logkey]);
      }
    }
    else
    {
      throw new Exception("Error: Called WriteBackLogs while logging was still off.");
    }

  }
}
