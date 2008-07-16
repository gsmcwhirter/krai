<?php
/**
 * The application configuration file.
 * @package Demo
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Structure of an Access Scheme for use with authenticating permissions.
 * @package Demo
 * @subpackage Lib
 *
 */
class AccessScheme extends Krai_Struct
{
  /**
   * Holds the required attributes
   * @var array
   */
  protected $FIELDS = array(
	  'requires' => array()
  );
}
