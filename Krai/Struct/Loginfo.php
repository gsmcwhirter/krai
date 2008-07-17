<?php
/**
 * Logging information struct for the Krai Framework.
 *
 * This file contains the information needed to configure the {@link Krai_Log}
 *
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Log Info Struct
 *
 * This struct contains the information for configuring the {@link Krai_Log}
 *
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Loginfo extends Krai_Struct
{
  protected $FIELDS = array(
    "types" => array(),
    "configs" => array(),
    "default" => ""
  );

}
