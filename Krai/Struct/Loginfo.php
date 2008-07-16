<?php
/**
 * Logging information struct for the Krai.
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Log Info Struct
 *
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Loginfo extends Krai_Struct
{

  /**
   * Allowed fields
   *
   * @var array
   */
  protected $FIELDS = array(
    "types" => array(),
    "configs" => array(),
    "default" => ""
  );

}
