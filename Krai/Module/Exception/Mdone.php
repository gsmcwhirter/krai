<?php
/**
 * Krai module done exception class
 * @package Krai
 * @subpackage Module
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * Framework module done exception
 *
 * @package Krai
 * @subpackage Module
 */
class Krai_Module_Exception_Mdone extends Krai_Base_Exception
{
  public function __construct($message = "", $code = 0)
  {
    parent::__construct($message, $code);
  }
}
