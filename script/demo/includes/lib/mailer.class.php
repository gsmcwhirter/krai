<?php
/**
 * The application configuration file.
 * @package Demo
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008
 */

/**
 * A Krai_Mail wrapper -- you can add stuff into the Send function here like a database record write
 * @package Demo
 * @subpackage Lib
 */
class Mailer extends Krai_Mail
{

  public static function Send(Krai_Struct_Mail $m)
  {
    return parent::Send($m);
  }

}
