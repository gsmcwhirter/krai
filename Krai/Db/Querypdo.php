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
class Krai_Db_Querypdo extends Krai_Db_Query
{

  protected $_numrows;

  /**
   * Constructor.  Saves the passed query to the instance
   *
   * @param mixed $query
   */
  function __construct($query, $numrows)
  {
    parent::__construct($query);
    $this->_numrows = (int)$numrows;
  }

  public function Close()
  {
    $this->_Query = null;
    $this->_Closed = true;
  }

  /**
   * Get the number of rows
   *
   */
  public function NumRows()
  {
    return $this->_numrows;
  }

}
