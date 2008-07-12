<?php
/**
 * Database query struct for the Krai.
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * Database update query struct
 *
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Dbquery_Update extends Krai_Struct_Dbquery
{

  /**
   * Allowed fields
   *
   * @var array
   */
  protected $FIELDS = array(
    "conditions" => "",
    "limit" => "",
    "fields" => array(),
    "parameters" => array(),
    "literals" => array()
  );

  /**
   * Query action
   *
   * @var string
   */
  public $action = "update";
}
