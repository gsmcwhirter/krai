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
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
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
class Krai
{
	/**#@+
	 * Logger level constant. These are used with {@link Krai_Log}.
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
	 * This variable holds the value of the path to the application includes
	 * directory. It gets set to {@link Krai::$APPDIR}/includes. The value has no
	 * trailing slash.
	 *
	 * @var string
	 */
	public static $INCLUDES;

	/**
	 * Application modules directory variable
	 *
	 * This variable holds the value of the path to the application modules
	 * directory. It gets set to {@link Krai::$INCLUDES}/modules. The value has no
	 * trailing slash.
	 *
	 * @var string
	 */
	public static $MODULES;

	/**
	 * Application layouts directory variable
	 *
	 * This variable holds the value of the path to the application layouts
	 * directory. It gets set to {@link Krai::$INCLUDES}/layouts. The value has no
	 * trailing slash.
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
	 * This variable holds an instance of {@link Krai_Lib_Inflector}, which can be
	 * used to convert from WordsWithUnderscores |-> words_with_underscores and
	 * back, among other functionality.
	 *
	 * @var Krai_Lib_Inflector
	 */
	public static $INFLECTOR = null;

	/**
	 * The page requested
	 *
	 * This variable holds the {@link Krai_Request} of the page that was requested.
	 * It is determined either by passing a string to {@link Krai::Run()}, or else
	 * automatically set by the call to {@link Krai::DetermineRequest()} from
	 * within {@link Krai::Run()}.
	 *
	 * @var Krai_Request
	 */
	public static $REQUEST;

	/**
	 * The cache system
	 *
	 * This variable holds the {@link Krai_Cache} instance that the framework
	 * will use.
	 *
	 * @var Krai_Cache
	 */
	private static $_CACHE;

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
	 * This is a flag representing whether or not application-level logging has
	 * been enabled and initialized in the framework. It is used by
	 * {@link Krai::WriteLog()} to determine whether to try writing the log
	 * message or whether to store it for writing when the logger is initialized.
	 *
	 * @var boolean
	 */
	private static $_LOGGING;

	/**
	 * Framework log message cache
	 *
	 * This is an array in which to hold log messages that are attempted to be
	 * sent to the logger for writing when the logger is not ready.
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
	 * This is a flag representing whether or not {@link Krai::Setup()} has yet
	 * been called. This prevents things from being set up twice.
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
	 * Input scrubbing class instance
	 *
	 * This variable holds an instance of the input scrubber. It is used in the
	 * {@link Krai::Run()} method.
	 *
	 * @var Nakor
	 */
	//private static $_NAKOR_CORE;

	/**
	 * Holds the database connections
	 *
	 * This is either an associative array of database connections as defined in
	 * the config file and initialized in {@link Krai::Run()}, or a single
	 * database if only one was defined.
	 *
	 * @var mixed
	 */
	public static $DB = null;

	/**
	 * Holds the default database connection by reference
	 *
	 * This variable holds the instance of the first defined database connection
	 * in {@link Krai::$DB}.
	 *
	 * @var Krai_Db
	 */
	public static $DB_DEFAULT = null;

	/**
	 * Holds the errors and notices
	 *
	 * This array holds the error and notice messages reported by the framework
	 * and application.
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
	 * Gets information from the configuration array {@link Krai::$_CONFIG}.
	 *
	 * This method retrieves the values set in the configuration file passed to
	 * {@link Krai::Setup()}. For instance, if the configuration file had the line
	 * <code>
	 * # snippet from the config file
	 * BASEURI: myapp
	 * </code>
	 * Then you can do the following:
	 * <code>
	 * print Krai::GetConfig("BASEURI");
	 * # => myapp
	 * </code>
	 *
	 * You can only retrieve the first level of information from the configuration
	 * file in this manner. To get lower levels, do the following:
	 * <code>
	 * # snippet from the config file
	 * CONFIG_CACHE:
	 *   DIR: /some/path
	 *   TIMEOUT: 3600
	 *
	 * # Getting those configuration settings
	 * $config_cache = Krai::GetConfig("CONFIG_CACHE");
	 * print $config_cache["DIR"];
	 * # => /some/path
	 * </code>
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
	 * It expects a parameter of a string of the location and name of the YAML
	 * configuration file. If the application skeleton is being used, this should
	 * be "../includes/configs/krai.yml" from the usual
	 * {@link Demo#default.php default.php} location.
	 *
	 * The function sets up the {@link Krai::$APPDIR}, {@link Krai::$FRAMEWORK},
	 * {@link Krai::$INCLUDES}, {@link Krai::$MODULES}, and {@link Krai::$LAYOUTS}
	 * variables, as well as starts a PHP session (unless disabled) and an output
	 * buffer (unless disabled).
	 *
	 * @param string $_conf_file The path to the configuration file.
	 * @throws Exception
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

			self::Uses(self::$FRAMEWORK."/Exception.php");


			spl_autoload_register(array("Krai","AutoLoad"));
			if(function_exists("__autoload"))
			{
				spl_autoload_register("__autoload");
			}

			self::$_SETUP = true;
		}
	}

	/**
	 * Makes everything start up and work
	 *
	 * This function starts everything in motion. According to the configuration,
	 * it loads and initializes all necessary and sufficient framework components.
	 * It initializes database connections, and finally initializes scrubbed
	 * versions of input variables.
	 *
	 * @param string $_uri An override for the usually determined request to
	 * process
	 * @throws Exception
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

			self::Uses(
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
				self::Uses(self::$FRAMEWORK."/Cache.php");

				$cconf = self::GetConfig("CONFIG_CACHE");
				self::$_CACHE = new Krai_Cache($cconf);
			}
			else
			{
				self::$_CACHE = null;
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

			if(count($DB) == 1)
			{
				self::$DB = array_shift($DB);
				self::$DB_DEFAULT =& self::$DB;
			}
			else
			{
				self::$DB = $DB;
				self::$DB_DEFAULT =& self::$DB[array_shift(array_keys($DB))];
			}

			if(self::GetConfig("USE_MAIL"))
			{
				$mconf = self::GetConfig("CONFIG_MAIL");

				self::Uses(self::$FRAMEWORK."/Mail.php");

				$mconfstruct = new Krai_Struct_Mailconf($mconf["MAILER_CONFIG"]);

				Krai_Mail::Configure(
					$mconf["SEND_MAIL"],
					$mconf["FROM_ADDR"],
					$mconf["FROM_NAME"],
					$mconfstruct
				);

			}

			if(!self::$INFLECTOR instanceOf Krai_Lib_Inflector)
			{
				self::$INFLECTOR = new Krai_Lib_Inflector();
			}

			//self::$_NAKOR_CORE = new Nakor();

			if(is_null($_uri))
			{
				$_uri = self::DetermineRequest();
			}

			if(is_null($_uri))
			{
				$_uri = "/";
			}

			self::Uses(self::$FRAMEWORK."/Request.php");

			Krai_Request::Init();
			self::$REQUEST = new Krai_Request($_GET, $_POST, $_SERVER, $_uri);
			//self::$REQUEST = new Krai_Request(self::$_NAKOR_CORE->CleanInput("GET"), self::$_NAKOR_CORE->CleanInput("POST"), $_SERVER, $_uri);

			try
			{
				self::$ROUTER = Krai_Router::Instance(self::GetConfig("DEFAULT_EXTENSION"));
			}
			catch(Exception $e)
			{
				self::$ROUTER = Krai_Router::Instance();
			}



			self::ReloadMessages();
			self::$ROUTER->DoRoute(self::$REQUEST);
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
	 *
	 * This function is used to set the Content-type header for a response. It is
	 * most often used when the script is outputting an image or JSON. If it is
	 * not called by the application on a given request, the Content-type defaults
	 * to "text/html" for compatibility. However, some users may want to use
	 * "application/xhtml+xml" for strict XHTML compliance.
	 *
	 * @param string $type The mime-type to set
	 * @param boolean $force Flag to force a reset if the type had previously been
	 * set
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
	 * This function cleans everything up, including closing logs, commiting the
	 * php session, and setting a Content-type if one was not set in the processed
	 * request. Additionally, it flushes the output buffer, and exits the program.
	 *
	 */
	private static function EndRun()
	{
		self::SaveMessages();
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
	 * Determines the request to be used from server variables.
	 *
	 * This function parses the {@link PHP_MANUAL#$_SERVER} REQUEST_URI variable
	 * in order to determine what request was actually made (taking into account
	 * the BASEURI set in the configuration file). It is called in
	 * {@link Krai::Run()} unless an overriding request is passed to that function
	 *
	 * @return string The actual request to parse.
	 */
	private static function DetermineRequest()
	{
		//get rid of the query string for these purposes
		$the_request = preg_replace("#\?.*$#", "", $_SERVER['REQUEST_URI']);

		$the_request_actual = preg_replace(array(
												 "#^/*#",
												 "#^".self::GetConfig("BASEURI")."#"
												),
										   array(
												 "",
												 ""
												),
										   $the_request
										  );
		return ($the_request_actual == "") ? "/" : $the_request_actual;
	}

	/**
	 * A wrapper for including files and logging such. Uses
	 * {@link PHP_MANUAL#func_get_args()} for variable argument number.
	 *
	 * This function accepts an unlimited number of arguments, each of which
	 * should be a string of a file that needs to be included into the application
	 * execution. The function uses {@link PHP_MANUAL#include_once}, so it is not
	 * suitable for files that need to be included multiple times.
	 *
	 * Additionally, the function provides logging for all the includes processed
	 * through it, at the {@link Krai::LOG_DEBUG} level.
	 *
	 * @return boolean Whether or not the file was included successfully
	 * @throws Exception
	 */
	public static function Uses()
	{
		$args = func_get_args();

		foreach($args as $filename)
		{
			if(preg_match("#^pear://#", $filename))
			{
				try
				{
					$pp = self::GetConfig("PEAR_PATH");
				}
				catch(Exception $e)
				{
					$pp = "";
				}
				$filename = $pp.substr($filename, 7);
			}


			if(!(@include_once $filename))
			{
				Krai::WriteLog("Failed loading file: ".$filename);

				throw new Exception("Load files failed for ".$filename);
				return false;
			}

			Krai::WriteLog("Loading file: ".$filename, Krai::LOG_DEBUG);
		}

		return true;
	}

	/**
	 * A function to implode an associative array preserving keys
	 *
	 * This function is an extension of {@link PHP_MANUAL#implode} for use when
	 * you want to preserve the keys of the array as well.
	 *
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
	 *
	 * This function is a wrapper for the logger currently in use (namely,
	 * {@link Krai_Log}). If the logger has been started, it writes to the log
	 * right away. Otherwise, it writes to the {@link Krai::$_BACKLOGS} array, so
	 * the log entries can be written if/when the logger gets initialized.
	 *
	 * @param string $message The message to write
	 * @param integer $level The level of the message
	 * @param array $logs The identifiers of the logs to which to write (default
	 * is the default log)
	 * @param string $cat The category of the log message
	 * @param array $forces I forget... the logger is being rewritten anyhow, so
	 * this will probably change...
	 *
	 */
	public static function WriteLog($message,
									$level = Krai::LOG_INFO,
									array $logs = array(),
									$cat = null,
									array $forces = array())
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
	 * This function writes the messages stored up in {@link Krai::$_BACKLOGS} to
	 * the logger. If this is called and the logger is still not initialized, an
	 * Exception is thrown.
	 *
	 * @throws Exception
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
			throw new Exception(
						  "Error: Called WriteBackLogs while logging was still off."
						  );
		}

	}

	/**
	 * Loads the file for a module or action name
	 *
	 * This function attempts to load the file containing the class named by the
	 * parameter. It is configured to have success with modules and actions placed
	 * in the usual places.
	 *
	 * @param string $_class The name of the class
	 * @return boolean The success of the loading
	 * @throws Exception
	 *
	 */
	public static function AutoLoad($_class)
	{
		if(!self::$INFLECTOR instanceOf Krai_Lib_Inflector)
		{
			self::$INFLECTOR = new Krai_Lib_Inflector();
		}
		$_class = self::$INFLECTOR->Camel2Underscore($_class);
		if(substr($_class,-6) == "module")
		{
			self::LoadModuleFile(substr($_class, 0, -7));
		}
		elseif(substr($_class, -6) == "action")
		{
			list($mod, $act) = explode("module", $_class, 2);
			self::LoadActionFile(substr($mod,0,-1), substr($act, 1,-7));
		}
		else
		{
		  //throw new Exception("Load failed for class ".$class);
		}
	}

	/**
	 * Tries to load the file for a module
	 *
	 * This function attempts to load the file containing a certain module.
	 *
	 * @param string $_module The name of the module
	 * @return boolean The success of the loading
	 * @throws Krai_Router_Exception
	 */
	private static function LoadModuleFile($_module)
	{
		$f = self::$MODULES."/".$_module.".module/".$_module.".module.php";
		if(self::Uses($f))
		{
			return true;
		}
		else
		{
		  //throw new Exception("Module Load failed for file ".$f);
		}
	}

	/**
	 * Tries to load the file for an action
	 *
	 * This function attempts to load the file containing a certain action.
	 *
	 * @param string $_module The name of the module of the action
	 * @param string $_action The name of the action
	 * @return boolean The success of the loading
	 * @throws Exception
	 */
	private static function LoadActionFile($_module, $_action)
	{
		$f = self::$MODULES."/".$_module.".module/actions/".$_action.".action.php";
		if(self::Uses($f))
		{
			return true;
		}
		else
		{
		  //throw new Exception("Action Load failed for file ".$f);
		}
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
	private static function SaveMessages()
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
	 * This function returns the error messages currently in the queue and clears
	 * the queue.
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

	/**
	 * Execute a redirect behind the scenes
	 *
	 * This function executes a redirect to another module/action pair behind the
	 * scenes. The browser is not redirected, just the script execution.
	 *
	 * @param string $module The module to redirect to
	 * @param string $action The action to execute
	 * @param array $params The parameters for the request
	 */
	public static function InternalRedirect($module, $action, array $params = array())
	{
		self::$REQUEST = new Krai_Request($_GET, $_POST, $_SERVER, self::$REQUEST->Uri());
		self::$ROUTER->ExecuteRoute(self::$REQUEST, $module, $action, $params);
	}

	public static function CacheFile($contents)
	{
		$qs = self::$REQUEST->Server("QUERY_STRING");
		if ($qs != "" && $qs != "?")
		{
			return false;
		}
		elseif(is_null(self::$_CACHE))
		{
			return true;
		}
		else
		{
			return self::$_CACHE->CacheFile(self::$REQUEST->Uri(),$contents);
		}
	}

	public static function ExpireCache($file_or_dir)
	{
		if(is_null(self::$_CACHE))
		{
			return true;
		}
		else
		{
			return self::$_CACHE->ExpireCache($file_or_dir);
		}
	}
}
