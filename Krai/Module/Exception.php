<?php
/**
 * Krai module handler exception class
 * @package Krai
 * @subpackage Module
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Framework module handler exception
 *
 * This is the exception thrown within the Krai_Module and Krai_Action classes.
 *
 * @package Krai
 * @subpackage Module
 */
class Krai_Module_Exception extends Krai_Exception
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

  /**
   * Error code for a problem concerning file paths
   *
   */
  const FilePathError = -6;
}
