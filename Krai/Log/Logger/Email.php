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
 * Logs to email
 *
 * @package Krai
 * @subpackage Log
 */
class Krai_Log_Logger_Email extends Krai_Log_Logger
{
    /**
     * Email to
     *
     * @var string
     */
    protected $recipient;
    /**
     * Email from
     *
     * @var string
     */
    protected $sender;
    /**
     * Email subject
     *
     * @var string
     */
    protected $subject;
    /**
     * Email content
     *
     * @var string
     */
    protected $mailbody;

    /**
     * Constructor
     *
     * @param mixed $parent_logger_instance
     * @param string $parent_application
     */
    public function __construct(&$parent_logger_instance, $parent_application)
    {
        parent::__construct ($parent_logger_instance, $parent_application);
        $this->EmailSetRecipient ( Krai_Log::DEFAULT_EMAIL_RECIPIENT );
        $this->EmailSetSender ( Krai_Log::DEFAULT_EMAIL_SENDER );
        $this->EmailSetSubject ( Krai_Log::DEFAULT_EMAIL_SUBJECT );
        $this->type = "email";
    }

    /**
     * Set recipient
     *
     * @param string $recipient
     */
    public function EmailSetRecipient ( $recipient )
    {
        $this->recipient = $recipient;
        $this->needs_reset = true;
    }

    /**
     * Set sender
     *
     * @param string $sender
     */
    public function EmailSetSender ( $sender )
    {
        $this->sender = $sender;
        $this->needs_reset = true;
    }

    /**
     * Set subject
     *
     * @param string $subject
     */
    public function EmailSetSubject ( $subject )
    {
        $this->subject = $subject;
        $this->needs_reset = true;
    }

    /**
     * Doesnt do anything
     *
     */
    public function TypeSpecificReset()
    {
    }

    /**
     * Initializes the instance
     *
     * @return boolean true
     */
    public function TypeSpecificOpen()
    {
        unset ($this->mailbody);
        return ( true );
    }

    /**
     * Writes a message to the email body
     *
     * @param array $entry
     */
    public function TypeSpecificOutputMsg(array $entry)
    {
        $severity = $entry["MSG_SEVERITY"];
        $this->mailbody .=  str_pad($entry["MSG_TIME"], 36).str_pad($entry["MSG_CATEGORY"], 13).str_pad($this->syslog_levels[$severity], 13).$entry["MSG_MSG"]."\n";
    }

    /**
     * Send the mail and clean up
     *
     * @return boolean
     */
    public function TypeSpecificClose()
    {
        if(!mail($this->recipient, $this->subject, $this->mailbody, $this->sender, "-f".Krai::GetConfig("ADMIN_EMAIL")))
        {
            $msg = "EMAILING FAILED: to ".$this->recipient.", from ".$this->sender.", subject ".$this->subject.", body ".$this->mailbody;
            $this->WriteLog($msg);
            return ( false );
        }
        return ( true );
    }

}
