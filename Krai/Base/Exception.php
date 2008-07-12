<?php
/**
 * Krai exception base class
 * @package Krai
 * @subpackage Base
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * Framework base exception
 *
 * @package Krai
 * @subpackage Base
 */
class Krai_Base_Exception extends Exception
{
  /**
   * Holds an array of errors
   *
   * @var array
   */
  public $Errors = array();

  /**
   * Constructor
   *
   * @param string $message
   * @param integer $code
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
