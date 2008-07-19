<?php
/**
 * Krai router exception class
 * @package Krai
 * @subpackage Router
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Router exception
 *
 * This is the exception type that should be thrown by things related to Krai_Router
 *
 * @package Krai
 * @subpackage Router
 */
class Krai_Router_Exception extends Krai_Exception
{
  /**
   * Error code for a route not found.
   *
   */
  const NoRouteFound = -1;

  /**
   * Error code for routing already having been performed.
   *
   */
  const RoutingPerformed = -2;

  /**
   * Error code for a route not yielding a module or action
   *
   */
  const NoRouteAction = -3;

  /**
   * Error code for not having an inflector to use
   *
   */
  const NoInflector = -4;
}
