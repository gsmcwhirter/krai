<?php
/**
 * Database query struct for the Krai Framework.
 *
 * This file contains the {@link Krai_Struct_Dbquery} for a SELECT query
 *
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Database select query struct
 *
 * This struct is the {@link Krai_Struct_Dbquery} representing a SELECT query
 *
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Dbquery_Select extends Krai_Struct_Dbquery
{

  protected $FIELDS = array(
    "conditions" => "",
    "order" => "",
    "limit" => "",
    "fields" => array(),
    "parameters" => array()
  );

  public $action = "select";
}
