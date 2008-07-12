<?php
/**
 * Database query struct for the Krai.
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Struct/Dbquery/Delete.php",
  Krai::$FRAMEWORK."/Struct/Dbquery/Find.php",
  Krai::$FRAMEWORK."/Struct/Dbquery/Insert.php",
  Krai::$FRAMEWORK."/Struct/Dbquery/Update.php"
);

/**
 * Database base query struct
 *
 * @package Krai
 * @subpackage Struct
 */
abstract class Krai_Struct_Dbquery extends Krai_Struct
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
    "include" => array(),
    "parameters" => array(),
    "literals" => array(),
    "multiple" => false
  );

  /**
   * Variables allowed on construct
   *
   * @var array
   */
  protected $VARS = array(
    "action",
    "tables"
  );

  /**
   * Query action
   *
   * @var string
   */
  public $action;

  /**
   * Query tables
   *
   * @var array
   */
  public $tables = array();

  /**
   * Constructor
   *
   * @param array $tables Tables for the query
   * @param array $new_defaults New default variable values
   */
  function __construct(array $tables, array $new_defaults = array())
  {
    if(array_key_exists("action", $this->FIELDS))
    {
      unset($this->FIELDS["action"]);
    }
    if(array_key_exists("tables", $this->FIELDS))
    {
      unset($this->FIELDS["tables"]);
    }
    parent::__construct($new_defaults);
    $this->tables = $tables;
  }
}
