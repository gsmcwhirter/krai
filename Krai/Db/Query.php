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
   * Constructor.  Saves the passed query to the instance
   *
   * @param mixed $query
   */
  function __construct($query)
  {
    $this->_Query = $query;
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
    if($m != "Close" && $m != "IsClosed")
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
    return $this->_Query->$v;
  }

  /**
   * Get a reference to the query object
   *
   * This function returns a reference to the query object wrapped in the instance
   */
  public function &GetQuery()
  {
    return $this->_Query;
  }

}
