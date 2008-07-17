<?php
/**
 * An access scheme struct
 *
 * This file holds a struct representing an access requirement scheme
 *
 * @package Demo
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Structure of an Access Scheme for use with authenticating permissions.
 *
 * This struct is used to represent an access requirement scheme.
 *
 * @package Demo
 * @subpackage Lib
 *
 */
class AccessScheme extends Krai_Struct
{
  protected $FIELDS = array(
	  'requires' => array()
  );
}
