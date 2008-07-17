<?php
/**
 * Logging base type for the Krai Framework.
 * @package Krai
 * @subpackage Log
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/*******************************************************
* File has been heavily modified from the original.    *
* Immediately below is the original heading of the file*
/*******************************************************
*  File: log_session_class.inc                         *
*  Authors: Jeremy Nelson and John Dunning             *
*  Date: April 22, 2003                                *
*******************************************************/

/**
 * Base log type
 *
 * @package Krai
 * @subpackage Log
 */
class Krai_Log_Logger{

    /**
     * Messages
     *
     * @var mixed
     */
    protected $messages;
    /**
     * Whether or not to reset stuff
     *
     * @var boolean
     */
    protected $needs_reset;
    /**
     * Instance of parent logger
     *
     * @var mixed
     */
    protected $parent_logger_instance;
    /**
     * Whether or not the instance is open for logging
     *
     * @var boolean
     */
    protected $instance_is_open;
    /**
     * Queue mode
     *
     * @var mixed
     */
    protected $queue_mode;
    /**
     * Log type
     *
     * @var mixed
     */
    protected $type;
    /**
     * Threshold
     *
     * @var mixed
     */
    protected $thresh;

    /**
     * Constructor
     *
     * @param mixed $parent_logger_instance
     * @param string $parent_application
     */
    public function __construct(&$parent_logger_instance, $parent_application)
    {
        $this->parent_logger_instance = &$parent_logger_instance;
        $this->parent_application = $parent_application;
        $this->syslog_levels = &$parent_logger_instance->syslog_levels;
        $this->thresh = Krai_Log::DEFAULT_THRESH;
        $this->queue_mode = Krai_Log::DEFAULT_QUEUE_MODE;
        $this->error_condition_trigger_thresh = Krai_Log::DEFAULT_ERROR_CONDITION_TRIGGER_THRESH;
        $this->error_condition_thresh = Krai_Log::DEFAULT_ERROR_CONDITION_THRESH;
        $this->in_error_condition = false;
        $this->max_queue_size = Krai_Log::DEFAULT_MAX_QUEUE_SIZE;
        $this->messages = array();
        $this->needs_reset = true;
    }

    /**
     * Open the log instance
     *
     * @return boolean
     */
    public function Open()
    {
        if ( $this->needs_reset )
        {
            $this->Reset();
        }
        if ( $this->TypeSpecificOpen() )
        {
            $this->instance_is_open = true;
            return (true);
        }
        else
        {
            $msg = sprintf ("Unable to open log instance\n");
            $this->WriteLog($msg);
            return (false);
        }
    }

    /**
     * Get the type of the instance
     *
     * @return string
     */
    public function GetType()
    {
        return ( $this->type );
    }

    /**
     * Set the queue size
     *
     * @param integer $size
     */
    public function SetMaxQueueSize($size)
    {
        $this->EnableQueueMode();
        $this->max_queue_size = $size;
    }

    /**
     * Set error condition threshold
     *
     * @param integer $thresh
     */
    public function SetErrorConditionTriggerThresh ($thresh)
    {
        $this->EnableQueueMode();
        $this->error_condition_trigger_thresh = $thresh;
    }

    /**
     * Set If error condition thresh, then what to log
     *
     * @param integer $thresh
     */
    public function SetErrorConditionThresh ($thresh)
    {
        $this->EnableQueueMode();
        $this->error_condition_thresh = $thresh;
    }

    /**
     * Set threshold for what to log
     *
     * @param integer $threshold
     */
    public function SetThresh($threshold)
    {
        $this->thresh = $threshold;
    }

    /**
     * Turn on queuing
     *
     */
    public function EnableQueueMode()
    {
        $this->queue_mode = true;
    }

    /**
     * Turn off queuing
     *
     */
    public function DisableQueueMode()
    {
        $this->FlushQueue();
        $this->queue_mode = false;
    }

    /**
     * Reset things
     *
     */
    public function Reset()
    {
        $this->Close();
        $this->TypeSpecificReset();
        $this->needs_reset = false;
    }

    /**
     * Add a message to the queue
     *
     * @param array $queue_entry
     */
    public function QueueMsg(array $queue_entry)
    {
        $queue_entry["MSG_TIME"] = date("r");
        array_push ($this->messages, $queue_entry);
        if ( $queue_entry["MSG_SEVERITY"] <= $this->error_condition_trigger_thresh )
        {
            $this->in_error_condition = true;
            $this->FlushQueue();
            $this->in_error_condition = false;
            return;
        }
        $queue_has_messages = true;
        while ( ( ( sizeof ( $this->messages ) > $this->max_queue_size ) | ( !$this->queue_mode ) ) & ( $queue_has_messages ) )
        {
            $queue_has_messages = $this->FlushMsg();
        }
        return;
    }

    /**
     * Flush a message from the log queue
     *
     * @return boolean
     */
    public function FlushMsg()
    {
        $entry = array_shift ($this->messages);
        if ($entry === null)
        {
            return (false);
        }
        if ( $entry["MSG_FORCE"] )
        { // Message was requested to be forced regardless of thresholds
            $this->TypeSpecificOutputMsg($entry);
            return (true);
        }
        if ( $this->in_error_condition )
        {
            $threshold = $this->error_condition_thresh;
        }
        else
        {
            $threshold = $this->thresh;
        }
        if ( $entry["MSG_SEVERITY"] <= $threshold )
        {
            $this->TypeSpecificOutputMsg($entry);
            return (true);
        }
        // Message did not meet any output criteria - but we did get a message off the queue so return true
        return (true);
    }

    /**
     * Log a message
     *
     * @param boolean $force
     * @param string $category
     * @param integer $severity
     * @param string $msg
     */
    public function Entry($force, $category, $severity, $msg)
    {
        if ( !$this->instance_is_open )
        {
            $this->Open();
        }
        $entry = array (    "MSG_FORCE"       =>  $force,
                            "MSG_CATEGORY"    =>  $category,
                            "MSG_SEVERITY"    =>  $severity,
                            "MSG_MSG"         =>  $msg
                        );
        $this->QueueMsg($entry);
    }

    /**
     * Flush the log queue
     *
     */
    public function FlushQueue()
    {
        do
        {
            $result = $this->FlushMsg();
        } while ($result);
    }

    /**
     * Close the logging instance
     *
     * @return boolean
     */
    public function Close()
    {
        if ( $this->instance_is_open )
        {
            $this->FlushQueue();
            $this->TypeSpecificClose();
            $this->instance_is_open = false;
            return (true);
        }
        else
        {
            // Well - we were already closed so nevermind
            return (true);
        }
    }

    /**
     * Write a message to the syslog
     *
     * @param string $msg
     */
    public function WriteLog($msg)
    {
        $this->parent_logger_instance->WriteLog($msg);
    }
}
