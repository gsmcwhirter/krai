<?php
/**
 * Database query struct for the Krai Framework.
 *
 * This file contains the {@link Krai_Struct_Dbquery} for a DELETE query
 *
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Database delete query struct
 *
 * This struct is the {@link Krai_Struct_Dbquery} representing a DELETE query
 *
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Dbquery_Delete extends Krai_Struct_Dbquery
{

  protected $FIELDS = array(
    "conditions" => "",
    "limit" => "",
    "parameters" => array()
  );

  public $action = "delete";
}
