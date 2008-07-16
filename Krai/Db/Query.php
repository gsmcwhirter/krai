<?php
/**
 * Database query object for the Krai.
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Database query object
 *
 * @package Krai
 * @subpackage Db
 */
class Krai_Db_Query
{
  /**
   * Is the query closed or not?
   *
   * @var boolean
   */
  protected $_Closed = false;
  /**
   * The query object
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
   * @return boolean
   */
  public function IsClosed()
  {
    return $this->_Closed;
  }

  /**
   * Close the query
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
   * @param mixed $v
   * @return mixed
   */
  public function __get($v)
  {
    return $this->_Query->$v;
  }

}
