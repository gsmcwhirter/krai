<?php
/**
 * Database query struct for the Krai.
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * Database select query struct
 *
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Dbquery_Find extends Krai_Struct_Dbquery
{

  /**
   * Allowed fields
   *
   * @var array
   */
  protected $FIELDS = array(
    "conditions" => "",
    "order" => "",
    "limit" => "",
    "fields" => array(),
    "parameters" => array()
  );

  /**
   * Query action
   *
   * @var string
   */
  public $action = "find";
}
