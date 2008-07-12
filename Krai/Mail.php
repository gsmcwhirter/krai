<?php
/**
 * E-Mailing utility
 * @package Krai
 * @subpackage Mail
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Struct/Mail.php"
);

/**
 * E-Mailing utility
 * @package Krai
 * @subpackage Mail
 */
abstract class Krai_Mail
{

  /**
   * Whether or not to actually send out email
   *
   * @var boolean
   */
  protected static $SEND_MAIL = false;

  /**
   * Default from address
   *
   * @var string
   */
  protected static $FROM = "";

  /**
   * Default sender name
   *
   * @var string
   */
  protected static $FROMNAME = "";

  /**
   * Factory to create a new mail struct
   * @return Krai_Struct_Mail
   */
  public static function NewMail()
  {
    return new Krai_Struct_Mail();
  }

  /**
   * Send (or just process and not send) an email
   * @param Krai_Struct_Mail $mail The email to send
   * @return boolean The result of the mail() call
   */
  public static function Send(Krai_Struct_Mail &$mail)
  {
    $mail->headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
    $mail->headers .= "From: ".(($mail->from_name) ? $mail->from_name : self::$FROMNAME)." <".(($mail->from) ? $mail->from : self::$FROM).">\r\n";
    $mail->headers .= "Reply-To: ".(($mail->from) ? $mail->from : self::$FROM)."\r\n";

    if(self::$SEND_MAIL)
    {
      return mail(implode(", ", $mail->recipients), $mail->subject, $mail->content, $mail->headers);
    }
    else
    {
      return true;
    }
  }

  /**
   * Configure the mailer
   *
   * @param boolean $_send Whether or not to send mail
   * @param string $_from From address
   * @param string $_fromname From name
   */
  public static function Configure($_send, $_from, $_fromname)
  {
    self::$SEND_MAIL = ($_send) ? true : false;
    self::$FROM = $_from;
    self::$FROMNAME = $_fromname;
  }
}
