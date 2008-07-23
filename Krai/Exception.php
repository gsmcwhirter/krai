<?php
/**
 * A Krai exception class
 * @package Krai
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Framework base exception
 *
 * This is an exception for Krai, and the exception from which any other in
 * the framework is derived.
 *
 * @package Krai
 */
class Krai_Exception extends Exception
{
  /**
   * Holds an array of errors
   *
   * This holds the error message(s) that are passed to the constructor.
   *
   * @var array
   */
  public $Errors = array();

  /**
   * Constructor
   *
   * This initializes the exception and records the error messages and code
   *
   * @param mixed $message A string error message or an array of string error
   * messages
   * @param integer $code The error code
   */
  public function __construct($message, $code = 0)
  {
    if(is_array($message))
    {
      parent::__construct("", $code);
      $this->Errors = $message;
    }
    else
    {
      parent::__construct($message, $code);
      $this->Errors = array($message);
    }
  }

}
