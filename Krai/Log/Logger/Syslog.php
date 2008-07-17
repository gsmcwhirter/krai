<?php
/**
 * Logging type for the Krai Framework.
 * @package Krai
 * @subpackage Log
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Logs to the system log
 *
 * @package Krai
 * @subpackage Log
 */
class Krai_Log_Logger_Syslog extends Krai_Log_Logger
{
    /**
     * No idea....
     *
     * @var mixed
     */
    protected $facility;

    /**
     * Constructor
     *
     * @param mixed $parent_logger_instance
     * @param string $parent_application
     */
    public function __construct(&$parent_logger_instance, $parent_application)
    {
        parent::__construct ($parent_logger_instance, $parent_application);
        $this->SyslogSetFacility ( Krai_Log::DEFAULT_SYSLOG_FACILITY );
        $this->queue_mode = false;
        $this->type = "syslog";
    }

    /**
     * Turn on queuing
     *
     * @return boolean false
     */
    public function EnableQueueMode()
    { // Queue mode is not valid for this queue type
        $msg = sprintf ("Attempt to enable queueing mode for log instance of type syslog\n");
        $this->WriteLog($msg);
        return (false);
    }

    /**
     * Set the facility to which to log
     *
     * @param mix $facility
     */
    public function SyslogSetFacility( $facility )
    {
        $this->facility = $facility;
        $this->needs_reset = true;
    }

    /**
     * Does nothing
     *
     */
    public function TypeSpecificReset()
    {
    }

    /**
     * Open the log
     *
     * @return boolean
     */
    public function TypeSpecificOpen()
    {
        openlog ($this->parent_application, LOG_ODELAY | LOG_PID | LOG_CONS, $this->facility );
        return ( true );
    }

    /**
     * Write a message to the log
     *
     * @param array $entry
     */
    public function TypeSpecificOutputMsg(array $entry)
    {
        // We have to pull this out because php doesn't like to use an array as an index to another array
        $severity = $entry["MSG_SEVERITY"];
        syslog ($severity,  str_pad($entry["MSG_CATEGORY"], 13).str_pad($this->syslog_levels[$severity], 13).$entry["MSG_MSG"]);
    }

    /**
     * Close the log
     *
     * @return boolean
     */
    public function TypeSpecificClose()
    {
        return ( closelog() );
    }

}
