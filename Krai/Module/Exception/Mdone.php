<?php
/**
 * Krai module done exception class
 * @package Krai
 * @subpackage Module
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Framework module done exception
 *
 * This is an exception thrown by a module to indicate to the framework that it
 * is done its execution sequence early and everything should be shut down.
 *
 * @package Krai
 * @subpackage Module
 */
class Krai_Module_Exception_Mdone extends Krai_Exception
{
  public function __construct($message = "", $code = 0)
  {
    parent::__construct($message, $code);
  }
}
