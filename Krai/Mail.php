<?php
/**
 * E-Mailing utility
 *
 * This file contails a wrapper class around the Mail PEAR module, hopefully
 * simplifying use with the framework.
 *
 * @package Krai
 * @subpackage Mail
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  "pear://Mail.php",
  Krai::$FRAMEWORK."/Struct/Mailconf.php",
  Krai::$FRAMEWORK."/Struct/Mail.php"
);

/**
 * E-Mailing utility
 *
 * This class is a wrapper around the PEAR Mail class. It can be overriden to
 * provide special functionality. First, it needs to be configured through
 * {@link Krai_Mail::Configure()}. After that, you can get a {@link Krai_Struct Mail}
 * to fill out by calling {@link Krai_Mail::NewMail()}. Finally, a Krai_Struct_Mail
 * instance can be sent via {@link Krai_Mail::Send()}.
 *
 * Theoretically, php internal mail, sendmail, and smtp sending are all possible,
 * though not thoroughly tested.
 *
 * @package Krai
 * @subpackage Mail
 */
abstract class Krai_Mail
{

  /**
   * PEAR Mail class instance
   *
   * This holds an instance of the PEAR Mail class, which is created and configured
   * in {@link Krai_Mail::Configure()}.
   *
   * @var Mail
   */
  protected static $_MAILER;

  /**
   * Whether or not to actually send out email
   *
   * This is a flag determining whether or not to actually send mail. This lets
   * you turn off mail sending without commenting out all the mailing parts of
   * the application code.
   *
   * @var boolean
   */
  protected static $_SEND_MAIL = false;

  /**
   * Default from address
   *
   * This holds the default address from which to send mail. It can be configured
   * in a {@link Krai_Mail::Configure()} call.
   *
   * @var string
   */
  protected static $_FROM = "";

  /**
   * Default sender name
   *
   * This holds the default name of the sending entity. It can be configured in
   * a {@link Krai_Mail::Configure()} call.
   *
   * @var string
   */
  protected static $_FROMNAME = "";

  /**
   * Default character set
   *
   * This holds the default charset in which to send mail. It can be configured
   * in a {@link Krai_Mail::Configure()} call inside the {@link Krai_Struct_Mailconf}
   * struct passed.
   *
   * @var string
   */
  protected static $_CHARSET = "iso-8859-1";

  /**
   * Factory to create a new mail struct
   *
   * This function generates and returns a new {@link Krai_Struct_Mail} instance,
   * which represents a mail that will later be sent.
   *
   * @return Krai_Struct_Mail
   */
  public static function NewMail()
  {
    return new Krai_Struct_Mail();
  }

  /**
   * Send (or just process and not send) an email
   *
   * This function processes a {@link Krai_Struct_Mail} and attempts to send it
   * if emailing is turned on.
   *
   * @param Krai_Struct_Mail $mail The email to send
   * @return boolean The success of the mailing attempt
   */
  public static function Send(Krai_Struct_Mail &$mail)
  {
    $headers = $mail->headers;
    $headers["Content-type"] = "text/plain; charset=".self::$_CHARSET;
    $headers["From"] = ($mail->from_name ? $mail->from_name : self::$_FROMNAME).
                        " <".(($mail->from) ? $mail->from : self::$_FROM).">";
    $headers["Reply-to"] = (($mail->from) ? $mail->from : self::$_FROM);
    $headers["Subject"] = $mail->subject;
    $headers["Date"] = date("r");

    if(self::$_SEND_MAIL)
    {
      $res = self::$_MAILER->send($mail->recipients, $headers, $mail->content);
      if($res instanceOf PEAR_Error)
      {
        return false;
      }
      else
      {
        return true;
      }
    }
    else
    {
      return true;
    }
  }

  /**
   * Configure the mailer
   *
   * This function lets you configure the mailer options. It should probably
   * only be called once, but that is not enforced in the code. This function
   * sets {@link Krai_Mail::$_SEND_MAIL}, {@link Krai_Mail::$_FROM},
   * {@link Krai_Mail::$_FROMNAME}, and {@link Krai_Mail::$_MAILER}
   *
   * @param boolean $_send Whether or not to send mail
   * @param string $_from From address
   * @param string $_fromname From name
   * @param Krai_Struct_Mailconf $_backend The mailer backend configuration
   * @throws Krai_Mail_Exception
   */
  public static function Configure($_send,
                                   $_from,
                                   $_fromname,
                                   Krai_Struct_Mailconf $_backend
                                  )
  {
    self::$_SEND_MAIL = ($_send) ? true : false;
    self::$_FROM = $_from;
    self::$_FROMNAME = $_fromname;
    self::$_CHARSET = $_backend->charset;

    switch($_backend->type)
    {
      case "internal":
        self::$_MAILER = @Mail::factory("mail", $_backend->args);
        break;
      case "sendmail":
        self::$_MAILER = @Mail::factory("sendmail",
                                       array("sendmail_path" => $_backend->sendmail_path,
                                             "sendmail_args" => $_backend->args
                                            )
                                      );
        break;
      case "smtp":
        self::$_MAILER = @Mail::factory("smtp", array(
          "host" => $_backend->smtp_host,
          "port" => $_backend->smtp_port,
          "auth" => $_backend->smtp_auth,
          "username" => $_backend->smtp_username,
          "password" => $_backend->smtp_password,
          "localhost" => $_backend->smtp_localhost,
          "timeout" => ($_backend->smtp_timeout == "") ? null : $_backend->smtp_timeout,
          "verp" => $_backend->smtp_verp,
          "debug" => $_backend->smtp_debug,
          "persist" => $_backend->smtp_persist
        ));
        break;
      default:
        throw new Krai_Mail_Exception("Unrecognized mailer type.");
    }

    if (self::$_MAILER instanceOf PEAR_Error)
    {
      self::$_SEND_MAIL = false;
      throw new Krai_Mail_Exception("Mailer instance did not initialize properly.");
    }

  }
}
