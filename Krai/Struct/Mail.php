<?php
/**
 * Struct holding the information for an e-mail
 *
 * This file contains a {@link Krai_Struct} representing the data for an email
 *
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Struct holding the information for an e-mail
 *
 * This struct contains the data necessary to send an email via {@link Krai_Mail}
 *
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Mail extends Krai_Struct
{
  protected $FIELDS = array(
    "recipients" => array(),
    "content" => "",
    "from" => "",
    "from_name" => "",
    "subject" => "",
    "headers" => array()
  );
}
