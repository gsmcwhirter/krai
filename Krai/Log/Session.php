<?php
/**
 * Logging session for the Krai Framework.
 * @package Krai
 * @subpackage Log
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Logging session
 *
 * @package Krai
 * @subpackage Log
 */
class Krai_Log_Session
{
    /**
     * Name of the parent application
     *
     * @var string
     */
    protected $_parent_application;
    /**
     * Categories of messages
     *
     * @var array
     */
    protected $_message_categories;
    /**
     * Logger levels
     *
     * @var array
     */
    public $syslog_levels;

    /**
     * Has the console (output to webpage) been started
     *
     * @var boolean
     */
    protected $_console_opened;
    /**
     * Queue of console messages
     *
     * @var mixed
     */
    protected $_console_msg_queue;
    /**
     * Console queue mode
     *
     * @var mixed
     */
    protected $_console_queue_mode;

    /**
     * Has an email log been opened
     *
     * @var boolean
     */
    protected $_email_opened;
    /**
     * Email log recipient
     *
     * @var mixed
     */
    protected $_email_log_recipient;
    /**
     * Email log sender
     *
     * @var mixed
     */
    protected $_email_log_sender;
    /**
     * Email log subject
     *
     * @var mixed
     */
    protected $_email_log_subject;
    /**
     * Email message queue
     *
     * @var mixed
     */
    protected $_email_msg_queue;
    /**
     * Email queue mode
     *
     * @var mixed
     */
    protected $_email_queue_mode;

    /**
     * Constructr
     *
     * @param string $parent_app
     */
    public function __construct($parent_app)
    {
        $this->_log_mode_array = explode(",", Krai_Log::LOG_MODES_LIST);
        $this->_message_categories = explode(",", Krai_Log::MESSAGE_CATEGORIES_LIST);
        $this->syslog_levels = explode(",", Krai_Log::SYSLOG_LEVELS_LIST);
        $this->_parent_application = $parent_app;
    }

    /**
     * Start a given log instance
     *
     * @param string $type
     * @return mixed
     */
    public function Enable($type)
    {
        switch ($type)
        {
            case "local":
                {
                    $this->_log_instances[] = new Krai_Log_Logger_Local($this, $this->_parent_application);
                    $instance_index = sizeof ($this->_log_instances) - 1;
                    break;
                }
            case "syslog":
                {
                    $this->_log_instances[] = new Krai_Log_Logger_Syslog($this, $this->_parent_application);
                    $instance_index = sizeof ($this->_log_instances) - 1;
                    break;
                }
            case "email":
                {
                    $this->_log_instances[] = new Krai_Log_Logger_Email($this, $this->_parent_application);
                    $instance_index = sizeof ($this->_log_instances) - 1;
                    break;
                }
            case "console":
                {
                    $this->_log_instances[] = new Krai_Log_Logger_Console($this, $this->_parent_application);
                    $instance_index = sizeof ($this->_log_instances) - 1;
                    break;
                }
            default:
                {
                    $this->WriteLog("php-logger->enable_log_instance called with invalid instance type ".$type."\n");
                    return (false);
                }
        }
        return ($instance_index);
    }

    /**
     * Disable a logger instance
     *
     * @param mixed $instance
     * @return boolean
     */
    public function Disable($instance)
    {
        if ( is_num ($instance) )
        {
            if ( $instance < sizeof ($this->_log_instances) )
            {
                $this->_log_instances[$instance]->Close();
            }
            else
            {
                $msg = sprintf ("php-logger->disable_log_instance called with non instantiated instance %s", $instance);
                $this->WriteLog ($msg);
                return (false);
            }
        }
        else
        {
            $msg = sprintf ("php-logger->disable_log_instance called with non integer parameter %s requires instance number", $instance);
            $this->WriteLog ($msg);
            return (false);
        }
    }

    /**
     * Write a log entry
     *
     */
    public function Write()
    {
        $numargs = func_num_args();

        $severity = null; //Just to ensure that we don't have bleedover between calls
        $category = false;
        $good_args = true;

        $override = array();

        switch ($numargs)
        {
            case 0:
            case 1:     $log_error_msg = "Function: log_entry called with 0 or 1 arguement, that's not good";
                        $this->WriteLog($log_error_msg);
                        $good_args = false;
                        break;

            case 2:     $inst = func_get_arg(0);
                        $msg = func_get_arg(1);
                        break;

            default:    $args = func_get_args();
                        $inst = $args[0];
                        for ($i = 1; $i < sizeof($args)-1; $i++)
                        {
                            $good_arg = false;
                            $arg = $args[$i];
                            if (ereg("[\+-].*", $arg)) // This conditional has to come before the severity test since "+/-[0-9]" return true for is_numeric
                            {
                                $action = substr($arg, 0, 1);
                                $requested_instance = substr($arg, 1);
                                for ($x = 0; $x < sizeof ($this->_log_instances); $x++)
                                {
                                    $instance = &$this->_log_instances[$x];
                                    // OK - this is stupid - but it's because PHP returns true on is_numeric ( string of ints) but does not return true on string of ints===ints
                                    if ( ( ( is_numeric ( $requested_instance ) )& ($requested_instance == $x ) ) | ( $requested_instance === $instance->GetType() ) )
                                    {
                                        $override[$x] = "$action";
                                        $good_arg = true;
                                    }
                                }
                                if (!$good_arg)
                                {
                                    $msg = sprintf ("instance override requested for non enabled instance %s with action %s\n", $requested_instance, $action);
                                    $this->WriteLog($msg);
                                }
                            }
                            elseif ((is_numeric($arg))&&($arg < 8)) // numeric values < 8 are always interpreted as syslog sev levels
                            {
                                if ($severity)
                                {
                                    $log_error_msg = "Severity was already set - cannot reset!";
                                    $this->WriteLog($log_error_msg);
                                    $good_arg = false;
                                }
                                else
                                {
                                    $severity = $arg;
                                    $good_arg = true;
                                }
                            }
                            else
                            {
                                foreach ($this->_message_categories as $message_category)
                                {
                                    if ($arg == $message_category)
                                    {
                                        $category = $arg;
                                        $good_arg = true;
                                    }
                                }
                            }
                            if (!$good_arg)
                            {
                                $good_args = false;
                            }
                        }
                        $msg = $args[sizeof($args)-1];
                        break;
        }
        if (!$good_args || !$this->CheckInst($inst))
        {
            $log_error_msg = "";
            $log_error_msg .= str_pad(date("r"), 36)." LOGGER CALLED WITH BAD ARGUMENTS from application ".$this->_parent_application."\n";
            for ($i = 0; $i < sizeof($args); $i++)
            {
                $log_error_msg .= "\tArguement #".$i." => ".$args[$i]."\n";
            }
            $this->WriteLog($log_error_msg);
        } else {

          if (!$category)
          {
              $category = "OTHER";
          }

          if ( $severity === null )
          {
              $severity = Krai_Log::DEFAULT_MSG_SEVERITY;
          }

          $orinst = false;

          foreach($override as $i => $val){
            $force = false;
            if($i == $inst){
              $orinst = true;
            }
            if($val == "+"){
              $force = false;
            }
            if(!($val == "-")){
              $this->_log_instances[$i]->Entry ($force, $category, $severity, $msg);
            }
          }

          if(!$orinst && (!array_key_exists($inst, $override) || $override[$inst] != "-")){
            $force = false;
            $this->_log_instances[$inst]->Entry ($force, $category, $severity, $msg);
          }
        }
    }

    /**
     * Configure a logging instance
     *
     * @return boolean
     */
    public function Configure ()
    {
        $found_valid_instance = false;

        $args = func_get_args();
        if ( sizeof( $args ) < 2 )
        {
            $msg = sprintf ("Configure instance requires at least 2 arguments - received %s", $args[0]);
            return (false);
        }

        $requested_instance = array_shift ($args);
        $function = array_shift ($args);
        $params = array_shift ($args);
        while ($param_temp = array_shift ($args) )
        {
            $params .= ", ".$param_temp;
        }

        for ($i = 0; $i < sizeof ($this->_log_instances); $i++)
        {
            $instance = &$this->_log_instances[$i];
            if ( ( $requested_instance === $i ) | ( $requested_instance === $instance->GetType() ) )
            { // OK - either the requested instance was one of our types or it was an integer for a particular instance
                if ( method_exists ($instance, $function) )
                {
                    $instance->$function($params);
                    $found_valid_instance = true;
                }
                else
                {
                    $msg = sprintf ("Attempt to use a non existant configuration %s public function for logger instance %s\n", $function, $requested_instance);
                    $this->WriteLog($msg);
                    return (false);
                }
            }
        }
        if ( !$found_valid_instance )
        {
            $msg = sprintf ("Attempt to modify a non existant logger instance %s\n", $requested_instance);
            $this->WriteLog($msg);
            return (false);
        }
        else
        {
            return (true);
        }
    }

    /**
     * Write a message from the logger to the syslog
     *
     * @param string $msg
     */
    public function WriteLog($msg)
    {
        echo ('LOGGER'.Krai::LOG_CRITICAL.$msg);
        openlog ("LOGGER", LOG_ODELAY | LOG_PID | LOG_CONS, Krai_Log::DEFAULT_LOGGER_LOG_FACILITY );
        syslog (Krai::LOG_CRITICAL, str_pad("LOGGER", 13).str_pad("CRITICAL", 13).$msg);
    }

    /**
     * Close all open logs
     *
     */
    public function Close()
    {
        foreach($this->_log_instances as $instance)
        {
            $instance->Close();
        }
    }

    /**
     * Is the index a log instance?
     *
     * @param integer $i
     * @return boolean
     */
    protected function CheckInst($i){
      if(!array_key_exists($i, $this->_log_instances)){
        return false;
      } else {
        return true;
      }
    }
}
