<?php
/**
 * Struct holding the information for an e-mail connection
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Struct holding the information for an e-mail connection
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Mailconf extends Krai_Struct
{
  /**
   * @var array The allowed data fields in the struct
   */
  protected $FIELDS = array(
    "type" => "",
    "charset" => "",
    "args" => array(),
    "sendmail_path" => "",
    "smtp_host" => "",
    "smtp_port" => "",
    "smtp_auth" => false,
    "smtp_username" => "",
    "smtp_password" => "",
    "smtp_localhost" => "",
    "smtp_timeout" => "",
    "smtp_verp" => false,
    "smtp_debug" => false,
    "smtp_persist" => false
  );
}
