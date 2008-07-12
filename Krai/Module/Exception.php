<?php
/**
 * Krai module handler exception class
 * @package Krai
 * @subpackage Module
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * Framework module handler exception
 *
 * @package Krai
 * @subpackage Module
 */
class Krai_Module_Exception extends Krai_Base_Exception
{
  /**
   * Error code for a processing error
   *
   */
  const ProcessingError = -1;

  /**
   * Error code for a validation error
   *
   */
  const ValidationError = -2;

  /**
   * Error code for a missing action
   *
   */
  const ActionNotFound = -3;

  /**
   * Error code for a error in a filter
   *
   */
  const FilterFailure = -4;

  /**
   * Error code for an unknown request method
   *
   */
  const UnknownRequestMethod = -5;
}
