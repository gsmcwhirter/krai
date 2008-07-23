<?php
/**
 * Logging interface for the Krai Framework.
 *
 * Apologies, but I am not going to document this well since it will be replaced
 * by version 1.1.
 *
 * @package Krai
 * @subpackage Log
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Log/Logger.php",
  Krai::$FRAMEWORK."/Log/Logger/Console.php",
  Krai::$FRAMEWORK."/Log/Logger/Email.php",
  Krai::$FRAMEWORK."/Log/Logger/Local.php",
  Krai::$FRAMEWORK."/Log/Logger/Syslog.php",
  Krai::$FRAMEWORK."/Log/Session.php",
  Krai::$FRAMEWORK."/Struct/Loginfo.php"
);

/**
 * Log output path
 *
 */
define ("KRAI_DEFAULT_LOCAL_LOGFILE_PATH", Krai::$APPDIR."/log/");

/**
 * Admin email constant
 */
define("KRAI_ADMIN_EMAIL", Krai::GetConfig("ADMIN_EMAIL"));

/**
 * Email log subject template.
 * The calling application is appended to the end of this
 *
 */
define ("KRAI_DEFAULT_EMAIL_SUBJECT", "LOG MESSAGE: ".date("r"));

/**
 * Logger interface
 *
 * Apologies, but I am not going to document this well since it will be replaced
 * by version 1.1.
 *
 * @package Krai
 * @subpackage Log
 */
abstract class Krai_Log
{
  /**
   * System log level-ish thing
   *
   */
  const DEFAULT_LOGGER_LOG_FACILITY = LOG_LOCAL6;
  /**
   * Default log severity
   *
   */
  const DEFAULT_MSG_SEVERITY = Krai::LOG_INFO;
  /**
   * Default category
   *
   */
  const DEFAULT_MSG_CATEGORY = "OTHER";
  /**
   * Default threshold
   *
   */
  const DEFAULT_THRESH = Krai::LOG_WARNING;
  /**
   * Default queue size
   *
   */
  const DEFAULT_MAX_QUEUE_SIZE = 250;
  /**
   * Default trigger threshold
   *
   */
  const DEFAULT_ERROR_CONDITION_TRIGGER_THRESH = Krai::LOG_ERROR;
  /**
   * Default error result threshold
   *
   */
  const DEFAULT_ERROR_CONDITION_THRESH = Krai::LOG_DEBUG;
  /**
   * Queue messages?
   *
   */
  const DEFAULT_QUEUE_MODE = false;
  /**
   * Default log name
   *
   */
  const DEFAULT_LOCAL_LOGFILE_NAME = "crash.log";
  /**
   * Default logging method
   *
   */
  const DEFAULT_LOCAL_METHOD = "ARCHIVE";
  /**
   * Default mail recipient
   *
   */
  const DEFAULT_EMAIL_RECIPIENT = KRAI_ADMIN_EMAIL;
  /**
   * Default mail sender
   *
   */
  const DEFAULT_EMAIL_SENDER = KRAI_ADMIN_EMAIL;

  /**
   * Default main subject
   */
  const DEFAULT_EMAIL_SUBJECT = KRAI_DEFAULT_EMAIL_SUBJECT;
  /**
   * Default syslog level
   */
  const DEFAULT_SYSLOG_FACILITY = LOG_LOCAL6;

  /**
   * List of message categories
   *
   */
  const MESSAGE_CATEGORIES_LIST = "LDAP,DATA,SIS,LOGIC,FTP,LOGS,SQL,OTHER,INTERNAL,MAIL";
  /**
   * List of log levels
   *
   */
  const SYSLOG_LEVELS_LIST = "LOG_EMERG,LOG_ALERT,LOG_CRITICAL,LOG_ERROR,LOG_WARNING,LOG_NOTICE,LOG_INFO,LOG_DEBUG";
  /**
   * List of log modes
   *
   */
  const LOG_MODES_LIST = "local,email,syslog,console";

  /**
   * The logger actual instance
   *
   * @var Krai_Log_Session
   */
  protected static $_LOGSESSION;

  /**
   * The state of the logger
   *
   * @var boolean
   */
  private static $_LCONNECTED = false;

  /**
   * The logs themselves
   *
   * @var array
   */
  public static $_LOGS = array();

  /**
   * The default log
   *
   * @var mixed
   */
  protected static $_LOGDEFAULT = null;

  /**
   * Initialize the logger
   *
   * @param Krai_Struct_Loginfo $loginfo
   */
  final public static function Start(Krai_Struct_Loginfo $loginfo)
  {
    if(!self::$_LCONNECTED)
    {
      self::$_LOGSESSION = new Krai_Log_Session("Krai");
      foreach($loginfo->types as $name => $type)
      {
        self::$_LOGS[$name] = self::$_LOGSESSION->Enable($type);
      }
      foreach($loginfo->configs as $key => $params)
      {
        $inst = "";
        foreach($params as $dir => $par)
        {
          if(substr($key,0,2) == "n:")
          {
            $temp = substr($key, 2);
            $inst = self::$_LOGS[$temp];
          }
          elseif(substr($key,0,2) == "t:")
          {
            $inst = substr($key,2);
          }

          if(is_null($par))
          {
            self::$_LOGSESSION->Configure($inst, $dir);
          }
          else
          {
            self::$_LOGSESSION->Configure($inst, $dir, $par);
          }

        }
      }
      self::$_LOGDEFAULT = $loginfo->default;
      self::$_LCONNECTED = true;
    }
  }

  /**
   * Write a log message
   *
   * @param string $message
   * @param integer $level
   * @param array $logs
   * @param string $cat
   * @param array $forces
   */
  final public static function Write($message,
                                     $level = Krai::LOG_INFO,
                                     array $logs = array(),
                                     $cat = null,
                                     array $forces = array())
  {
    $pars = array();
    if(count($logs) == 0)
    {
      $pars[0] = self::$_LOGS[self::$_LOGDEFAULT];
      foreach($forces as $f)
      {
        $pars[] = $f;
      }
      if(!is_null($cat))
      {
        $pars[] = $cat;
      }
      $pars[] = $level;
      $pars[] = $message;

      call_user_func_array(array(self::$_LOGSESSION, "Write"), $pars);
    }
    else
    {
      foreach($logs as $log)
      {
        $pars[0] = self::$_LOGS[$log];
        foreach($forces as $f)
        {
          $pars[] = $f;
        }
        if(!is_null($cat))
        {
          $pars[] = $cat;
        }
        $pars[] = $level;
        $pars[] = $message;
        call_user_func_array(array(self::$_LOGSESSION, "Write"), $pars);
      }
    }
  }

  /**
   * Close all the logs and stuff
   *
   * @return boolean
   */
  final public static function Close()
  {
    self::$_LOGSESSION->Close();
    return true;
  }

}
