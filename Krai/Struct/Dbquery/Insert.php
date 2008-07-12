<?php
/**
 * Database query struct for the Krai.
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * Database insert query struct
 *
 * @package Krai
 * @subpackage Struct
 */
class Krai_Struct_Dbquery_Insert extends Krai_Struct_Dbquery
{

  /**
   * Allowed fields
   *
   * @var array
   */
  protected $FIELDS = array(
    "fields" => array(),
    "multiple" => false
  );

  /**
   * Query action
   *
   * @var string
   */
  public $action = "insert";
}
