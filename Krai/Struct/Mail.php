<?php
/**
 * Struct holding the information for an e-mail
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Struct holding the information for an e-mail
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Mail extends Krai_Struct
{
  /**
   * @var array The allowed data fields in the struct
   */
  protected $FIELDS = array(
    "recipients" => array(),
    "content" => "",
    "from" => "",
    "from_name" => "",
    "subject" => "",
    "headers" => array()
  );
}
