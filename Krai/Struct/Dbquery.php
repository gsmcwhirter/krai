<?php
/**
 * Database query struct for the Krai Framework.
 *
 * This file contains the struct that is the root for the rest of the {@link Krai_Db}
 * query-type structs
 *
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Struct/Dbquery/Delete.php",
  Krai::$FRAMEWORK."/Struct/Dbquery/Select.php",
  Krai::$FRAMEWORK."/Struct/Dbquery/Insert.php",
  Krai::$FRAMEWORK."/Struct/Dbquery/Update.php"
);

/**
 * Database base query struct
 *
 * This struct is the base for the rest of the structs that are used by {@link Krai_Db}
 * for automatic query construction.
 *
 * @package Krai
 * @subpackage Struct
 */
abstract class Krai_Struct_Dbquery extends Krai_Struct
{

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
   * Query action
   *
   * This variable represents the general action of the query. It is a key for the
   * instance's handling by {@link Krai_Db_Handler::Process()}.
   *
   * @var string
   */
  public $action;

  /**
   * Query tables
   *
   * This variable contains the tables and joins involved in the query. It is
   * parsed by {@link Krai_Db_Handler::GetJoins()}.
   *
   * @var array
   */
  public $tables = array();

  /**
   * Constructor
   *
   * This overrides a standard {@link Krai_Struct::__construct()} call so that
   * tables must be passed in initialization.
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
