<?php
/**
 * Database query struct for the Krai Framework.
 *
 * This file contains the {@link Krai_Struct_Dbquery} for an INSERT query
 *
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Database insert query struct
 *
 * This struct is the {@link Krai_Struct_Dbquery} representing an INSERT query
 *
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Dbquery_Insert extends Krai_Struct_Dbquery
{

  protected $FIELDS = array(
    "fields" => array(),
    "multiple" => false
  );

  public $action = "insert";
}
