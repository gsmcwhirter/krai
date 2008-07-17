<?php
/**
 * Database query object for the Krai Framework.
 *
 * This file holds a database query handle wrapper class speecific to the PDO
 * implementation.
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
 * This class is a wrapper around a query handle, specific to the PDO database
 * handler.
 *
 * @package Krai
 * @subpackage Db
 */
class Krai_Db_Querypdo extends Krai_Db_Query
{

  /**
   * Number of rows in the result
   *
   * This variable holds the number of rows in the query result.
   *
   * @var integer
   */
  protected $_numrows;

  /**
   * Constructor.  Saves the passed query to the instance
   *
   * @param mixed $query
   * @param mixed $numrows
   */
  function __construct($query, $numrows)
  {
    parent::__construct($query);
    $this->_numrows = $numrows;
  }

  /**
   * This function closes the query
   *
   */
  public function Close()
  {
    $this->_Query = null;
    $this->_Closed = true;
  }

  /**
   * Get the number of rows
   *
   * This function returns the number of rows in the query result
   *
   * @return integer
   *
   */
  public function NumRows()
  {
    return $this->_numrows;
  }

}
