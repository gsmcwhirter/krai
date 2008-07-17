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
 * Console (Web page) logger
 *
 * @package Krai
 * @subpackage Log
 */
class Krai_Log_Logger_Console extends Krai_Log_Logger
{
    /**
     * Line break character
     *
     * @var string
     */
    protected $break_character;

    /**
     * Constructor - give the instance references to its parent etc
     *
     * @param mixed $parent_logger_instance
     * @param string $parent_application
     */
    public function __construct(&$parent_logger_instance, $parent_application)
    {
        parent::__construct($parent_logger_instance, $parent_application);
        $this->break_character = "";
        $this->queue_mode = false;
        $this->type = "console";
    }

    /**
     * Turn on queueing
     *
     * @return boolean false
     */
    public function EnableQueueMode()
    { // Queue mode is not valid for this queue type
        $msg = sprintf ("Attempt to enable queueing mode for log instance of type console\n");
        $this->WriteLog($msg);
        return (false);
    }

    /**
     * Set that the console is in fact the web
     *
     */
    public function ConsoleSetWeb ( )
    {
        $this->break_character = "<br />";
    }

    /**
     * Does nothing
     *
     */
    public function TypeSpecificReset()
    {
    }

    /**
     * Does nothing
     *
     * @return boolean true
     */
    public function TypeSpecificOpen()
    {
        return ( true );
    }

    /**
     * Actually output the message
     *
     * @param array $entry
     */
    public function TypeSpecificOutputMsg(array $entry)
    {
        // We have to pull this out because php doesn't like to use an array as an index to another array
        //Actually, it will, but might not have when this was written, so I won't change it b/c it works
        $severity = $entry["MSG_SEVERITY"];
        echo str_pad($entry["MSG_TIME"], 36).str_pad($entry["MSG_CATEGORY"], 13).str_pad($this->syslog_levels[$severity], 13).$entry["MSG_MSG"].$this->break_character."\n";
    }

    /**
     * Does nothing
     *
     */
    public function TypeSpecificClose()
    {
    }

}
