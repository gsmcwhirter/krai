<?php
/**
 * Database handler abstract class for the Krai Framework
 *
 * This file contains the abstract database handler interface that must be
 * implemented by any specific handlers.
 *
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Abstract database handler class
 *
 * This class is the basis for any database handlers that may be defined for
 * specific databases. It also provides template functions that may be overridden
 * if desired, and also provides factory methods for the {@link Krai_Struct_Dbquery}
 * structs.
 *
 * @package Krai
 * @subpackage Db
 */
abstract class Krai_Db_Handler
{

  /**
   * This variable holds the configuration data loaded by {@link Krai::Setup()}
   * and gathered when necessary from {@link Krai::GetConfig()} within {@link Krai_Db_Handler::__construct()}.
   *
   * @var array
   */
  protected $CONFIG = array();

  /**
   * Constructor.
   *
   * This function initializes and configures every descendent of this class. It
   * loads the configuration data for the databases.
   *
   * @todo Add logging
   */
  function __construct()
  {
    $this->CONFIG = Krai::GetConfig("CONFIG_DB");
  }

  /**
   * Execute an SQL query
   *
   * This function executes a specified SQL query using the passed parameters.
   *
   * @param string $querytype The type of query that is to be executed (one of "select","insert","update","delete","transaction" -- use "select" for things like DESCRIBE)
   * @param string $sql The query to execute
   * @param array $params Parameters for the query
   * @return mixed
   */
  abstract public function Query($querytype, $sql, array $params = array());

  /**
   * Fetch a database record from a query as an object
   *
   * This function fetches a row from the resultset of a query as an object
   *
   * @param Krai_Db_Query $qid The query
   * @return Krai_Db_Object The row (false if an error or no more rows)
   */
  abstract public function Fetch(Krai_Db_Query &$qid);

  /**
   * Fetch a database record from a query as an array
   *
   * This function fetches a row from the resultset of a query as an associative
   * array.
   *
   * @param Krai_Db_Query $qid The query
   * @return array The row (false if an error or no more rows)
   */
  abstract public function FetchArray(Krai_Db_Query &$qid);

  /**
   * Fetch just one column from the result.
   *
   * This is a holdover from something else that I do not recall. Leaving it for now.
   *
   * @param Krai_Db_Query $qid The query
   * @return mixed
   */
  abstract public function FetchOne(Krai_Db_Query &$qid);

  /**
   * Process a query struct according to its type.
   *
   * This function processes a query struct in the proper format for the database
   * adapter.
   *
   * @param Krai_Struct_Dbquery $query
   *
   */
  abstract public function Process(Krai_Struct_Dbquery $query);

  /**
   * Transaction controller
   *
   * This function wraps any transactional abilities the implementing adapter might
   * have.
   *
   * @param string $_action One of "start", "commit", or "rollback"
   *
   */
  abstract public function Transaction($_action);

  /**
   * Generate the joins from a table array
   *
   * This function takes an array of tables and joins and generates sql join fragements
   * from them. Any entry with a numerical key is included as a straight join. Any
   * entry with a string key is processed with the key being the table to do a
   * LEFT JOIN with, and the value being the condition(s) on which to join the
   * table.
   *
   * This can be overridden by specific adapters to generate the proper syntax
   * if it is not correct for that adapter.
   *
   * @param array $tables Array of tables to join
   * @return string The join syntax
   */
  protected function GetJoins(array $tables)
  {
    if(count($tables) == 1)
    {
      return $tables[0];
    }
    else
    {
      $main = array_shift($tables);
      $ljoins = array();
      $ijoins = array();
      foreach($tables as $k => $v)
      {
        if(is_string($k))
        {
          $ljoins[] = "LEFT JOIN ".$k." ON ".$v;
        }
        else
        {
          $ijoins[] = $v;
        }
      }

      array_unshift($ijoins, $main);

      $ljoins = implode(" ", $ljoins);
      $ijoins = implode(", ", $ijoins);

      return "(".$ijoins.") ".$ljoins;
    }
  }

  /**
   * Generate a query struct for a find query
   *
   * This function is a factory for {@link Krai_Struct_Dbquery_Find} structs
   *
   * @param mixed $tables
   * @return Krai_Struct_Dbquery_Find
   */
  final public function SelectQuery($tables)
  {
    if(!is_array($tables))
    {
      $tables = array($tables);
    }
    return new Krai_Struct_Dbquery_Select($tables);
  }

  /**
   * Generate a query struct for a delete query
   *
   * This function is a factory for {@link Krai_Struct_Dbquery_Delete} structs
   *
   * @param mixed $tables
   * @return Krai_Struct_Dbquery_Delete
   */
  final public function DeleteQuery($tables)
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
   * This function is a factory for {@link Krai_Struct_Dbquery_Insert} structs
   *
   * @param mixed $tables
   * @return Krai_Struct_Dbquery_Insert
   */
  final public function InsertQuery($tables)
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
   * This function is a factory for {@link Krai_Struct_Dbquery_Update} structs
   *
   * @param mixed $tables
   * @return Krai_Struct_Dbquery_Update
   */
  final public function UpdateQuery($tables)
  {
    if(!is_array($tables))
    {
      $tables = array($tables);
    }
    return new Krai_Struct_Dbquery_Update($tables);
  }
}
