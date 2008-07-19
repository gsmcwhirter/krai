<?php
/**
 * Database query object for the Krai Framework.
 *
 * This file contains the class that wraps around database query handles.
 *
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Database query object
 *
 * This class is a wrapper around database query handles.
 *
 * @package Krai
 * @subpackage Db
 */
class Krai_Db_Query
{
  /**
   * Is the query closed or not?
   *
   * This variable represents whether or not the query is closed.
   *
   * @var boolean
   */
  protected $_Closed = false;
  /**
   * The query object
   *
   * This variable holds the actual query handle.
   *
   * @var mixed
   */
  protected $_Query = null;

  /**
   * Holds query stats
   *
   * This variable holds statistics for a query such as number of rows in the result,
   * whether it was successful, the inserted ID, and the number of affected rows.
   *
   * @var array
   */
  protected $_Stats = array(
    "successful" => false,
    "insertid" => null,
    "affected" => null,
    "numrows" => null,
    "error" => array(null, null)
  );

  /**
   * Constructor.  Saves the passed query to the instance
   *
   * @param mixed $query
   */
  function __construct($query, $stats)
  {
    $this->_Query = $query;
    $this->_Stats = array_merge($this->_Stats, $stats);
  }

  /**
   * Is the query closed?
   *
   * This function reports whether or not the query is closed.
   *
   * @return boolean
   */
  public function IsClosed()
  {
    return $this->_Closed;
  }

  /**
   * Close the query
   *
   * This function closes the query.
   *
   */
  public function Close()
  {
    if(is_callable(array($this->_Query, "close"), false))
    {
      $this->_Query->close();
    }
    $this->_Query = null;
    $this->_Closed = true;
  }

  /**
   * PHP Magic Function
   *
   * This function passes method calls on an instance of this class onto the
   * query handle wrapped in the instance.
   *
   * @param mixed $m
   * @param mixed $p
   * @return mixed
   */
  public function __call($m, $p)
  {
    if($this->_Closed)
    {
      throw new Krai_Db_Exception("Tried to call method on closed query.");
    }
    else
    {
      if(is_callable(array($this->_Query, $m), false))
      {
          return call_user_func_array(array($this->_Query, $m), $p);
      }
      else
      {
        throw new Krai_Db_Exception("Unknown method called on a query.");
      }
    }

  }

  /**
   * PHP Magic Function
   *
   * This function passes property requests on an instance of this class onto the
   * query handle wrapped in the instance.
   *
   * @param mixed $v
   * @return mixed
   */
  public function __get($v)
  {
    if($this->_Closed)
    {
      return null;
    }
    else
    {
      return $this->_Query->$v;
    }
  }

  /**
   * Get a reference to the query object
   *
   * This function returns a reference to the query object wrapped in the instance
   *
   * @return mixed The Query object
   */
  public function &GetQuery()
  {
    return $this->_Query;
  }

  /**
   * Returns whether or not the query was successful
   *
   * This function returns the value of the {@link Krai_Db_Query::$_Stats}
   * "successful" key.
   *
   * @return boolean
   *
   */
  public function IsSuccessful()
  {
    return $this->_Stats["successful"];
  }

  /**
   * Returns the number of rows in the resultset
   *
   * This function returns the value of the {@link Krai_Db_Query::$_Stats}
   * "numrows" key.
   *
   * @return integer
   *
   */
  public function NumRows()
  {
    return $this->_Stats["numrows"];
  }

  /**
   * Returns the number of affected rows
   *
   * This function returns the value of the {@link Krai_Db_Query::$_Stats}
   * "affected" key.
   *
   * @return integer
   *
   */
  public function Affected()
  {
    return $this->_Stats["affected"];
  }

  /**
   * Returns the last inserted id
   *
   * This function returns the value of the {@link Krai_Db_Query::$_Stats}
   * "insertid" key.
   *
   * @return integer
   *
   */
  public function InsertID()
  {
    return $this->_Stats["insertid"];
  }

  /**
   * Returns query error information
   *
   * This function returns the value of the {@link Krai_Db_Query::$_Stats}
   * "error" key, formatted either as a string, as an error code, or an array of
   * (string, error code).
   *
   * @param string $ret How to return the information ("text","number", or "array")
   * @return mixed
   * @throws Krai_Db_Exception
   *
   */
  public function Error($ret = "text")
  {
    if($ret == "text")
    {
      return $this->_Stats["error"][0];
    }
    elseif($ret == "number")
    {
      return $this->_Stats["error"][1];
    }
    elseif($ret == "array")
    {
      return $this->_Stats["error"];
    }
    else
    {
      throw new Krai_Db_Exception("Un-recognized return type option passed to Krai_Db_Query::Error.");
    }
  }

}
