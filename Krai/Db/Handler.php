<?php
/**
 * Database handler abstract class for the Krai
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Abstract database handler class
 *
 * @package Krai
 * @subpackage Db
 */
abstract class Krai_Db_Handler
{
  protected $CONFIG = array();

  /**
   * Constructor.  Logs initialization.
   * @todo Add logging
   */
  function __construct()
  {
    $this->CONFIG = Krai::GetConfig("CONFIG_DB");
  }

  /**
   * Execute an SQL query
   *
   * @param string $sql The query to execute
   * @param boolean $resultset Whether to return a resultset or not
   * @param array $params Parameters for the query
   */
  abstract public function Query($sql, array $params = array());
  /**
   * Fetch a database record from a query as an object
   *
   * @param Krai_Db_Query $qid
   */
  abstract public function Fetch(Krai_Db_Query &$qid);
  /**
   * Fetch a database record from a query as an array
   *
   * @param Krai_Db_Query $qid
   */
  abstract public function FetchArray(Krai_Db_Query &$qid);
  /**
   * Get the number of rows returned for a query
   *
   * @param Krai_Db_Query $qid
   */
  abstract public function Rows(Krai_Db_Query $qid);
  /**
   * Get the error from a query
   *
   * @param string $ret
   */
  abstract public function Error($ret);
  /**
   * Return the number of rows affected by the last query
   *
   */
  abstract public function Affected();
  /**
   * Return the last inserted id
   *
   */
  abstract public function Inserted();
  /**
   * Escape the parameter so it is safe to insert into a query
   *
   * @param mixed $val
   */
  abstract public function Escape($val);
  /**
   * Process a query struct according to its type.
   *
   * @param Krai_Struct_Dbquery $query
   */
  abstract public function Process(Krai_Struct_Dbquery $query);

  /**
   * Generate a query struct for a find query
   *
   * @param mixed $tables
   * @return Krai_Struct_Dbquery_Find
   */
  public function FindQuery($tables)
  {
    if(!is_array($tables))
    {
      $tables = array($tables);
    }
    return new Krai_Struct_Dbquery_Find($tables);
  }

  /**
   * Generate a query struct for a delete query
   *
   * @param mixed $tables
   * @return Krai_Struct_Dbquery_Delete
   */
  public function DeleteQuery($tables)
  {
    if(!is_array($tables))
    {
      $tables = array($tables);
    }
    return new Krai_Struct_Dbquery_Delete($tables);
  }

  /**
   * Generate a query struct for an insert query
   *
   * @param mixed $tables
   * @return Krai_Struct_Dbquery_Insert
   */
  public function InsertQuery($tables)
  {
    if(!is_array($tables))
    {
      $tables = array($tables);
    }
    return new Krai_Struct_Dbquery_Insert($tables);
  }

  /**
   * Generate a query struct for an update query
   *
   * @param mixed $tables
   * @return Krai_Struct_Dbquery_Update
   */
  public function UpdateQuery($tables)
  {
    if(!is_array($tables))
    {
      $tables = array($tables);
    }
    return new Krai_Struct_Dbquery_Update($tables);
  }
}
