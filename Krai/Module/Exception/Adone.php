<?php
/**
 * Krai action done exception class
 * @package Krai
 * @subpackage Module
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Framework action done exception
 *
 * This exception is used to send a message to the module from an action instance
 * that the module should take over execution again.
 *
 * @package Krai
 * @subpackage Module
 */
class Krai_Module_Exception_Adone extends Krai_Exception
{

  /**
   * Code constant representing that neither a rendering nor a redirect occurred.
   *
   */
  const KMEADefault = 0;

  /**
   * Code constant representing that a rendering occurred.
   *
   */
  const Rendered = 1;

  /**
   * Code constant representing that a redirection occurred.
   *
   */
  const Redirected = 2;

  public function __construct($code = 0, $message = "")
  {
    parent::__construct($message, $code);
  }
}
