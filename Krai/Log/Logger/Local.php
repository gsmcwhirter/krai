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
 * Local file logger
 *
 * @package Krai
 * @subpackage Log
 */
class Krai_Log_Logger_Local extends Krai_Log_Logger
{
    /**
     * Path to the log
     *
     * @var string
     */
    protected $logfile_path;
    /**
     * Name of the log
     *
     * @var string
     */
    protected $logfile_name;
    /**
     * The actual file (path.filename)
     *
     * @var mixed
     */
    protected $logfile;
    /**
     * Logging mode
     *
     * @var mixed
     */
    protected $mode;
    /**
     * A File pointer
     *
     * @var mixed
     */
    protected $file_pointer;

    /**
     * Constructor
     *
     * @param mixed $parent_logger_instance
     * @param mixed $parent_application
     */
    public function __construct(&$parent_logger_instance, $parent_application)
    {
        parent::__construct ($parent_logger_instance, $parent_application);
        $this->LocalSetLogfilePath(KRAI_DEFAULT_LOCAL_LOGFILE_PATH);
        $this->LocalSetLogfileName(Krai_Log::DEFAULT_LOCAL_LOGFILE_NAME);
        $this->file_pointer = false;
        $this->type = "local";
    }

    /**
     * Set the log mode
     *
     * @param mixed $mode
     */
    public function LocalSetLogfileMode ($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Set the log file name
     *
     * @param string $logfile_name
     */
    public function LocalSetLogfileName($logfile_name)
    {
        $this->filename = $logfile_name;
        $this->needs_reset = true;
    }

    /**
     * Set the log path
     *
     * @param string $logfile_path
     */
    public function LocalSetLogfilePath($logfile_path)
    {
        if ( substr ($logfile_path, strlen($logfile_path) - 1) !== "/" )
        {
            $logfile_path = $logfile_path."/";
        }
        $this->path = $logfile_path;
        $this->needs_reset = true;
    }

    /**
     * Reset stuff
     *
     */
    public function TypeSpecificReset()
    {
        $this->logfile = $this->path.$this->filename;
    }

    /**
     * Open the logging instance
     *
     * @return boolean
     */
    public function TypeSpecificOpen()
    {
        if (file_exists($this->logfile))
        {
            if ($this->mode == "ARCHIVE")
            {
                rename($this->logfile, $this->logfile.date("Ymd-Gis"));
            }
        }
        if ($this->file_pointer = fopen ($this->logfile, "a+"))
        {
            $entry["MSG_TIME"] = date("r");
            $entry["MSG_CATEGORY"] = "LOGS";
            $entry["MSG_SEVERITY"] = Krai::LOG_INFO;
            $entry["MSG_MSG"] = "LOG OPENED, REQUESTED BY APPLICATION ".$this->parent_application;
            $this->TypeSpecificOutputMsg($entry);
        }
        else
        {
            $log_error_msg = "Unable to open log file ".$this->filename;
            parent::WriteLog($log_error_msg);
            return (false);
        }
        return (true);
    }

    /**
     * Write a message to the log
     */
    public function TypeSpecificOutputMsg($entry)
    {
        // We have to pull this out because php doesn't like to use an array as an index to another array
        $severity = $entry["MSG_SEVERITY"];
        fputs ($this->file_pointer, str_pad($entry["MSG_TIME"], 36).str_pad($entry["MSG_CATEGORY"], 13).str_pad($this->syslog_levels[$severity], 13).$entry["MSG_MSG"]."\n");
    }

    /**
     * Close the log file
     *
     * @return boolean
     */
    public function TypeSpecificClose()
    {
        return ( fclose ($this->file_pointer) );
    }

}
