<?php
/**
 * Database data object for the Krai.
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Database data object
 *
 * @package Krai
 * @subpackage Db
 */
class Krai_Db_Object
{

  /**
   * Holds the actual data
   *
   * @var array
   */
  private $DATA = array();

  /**
   * Constructor.  Does nothing
   *
   */
  function __construct()
  {}

  /**
   * PHP Magic function
   *
   * @param mixed $nm Name of the value
   * @return mixed
   */
  public function __get($nm)
  {
    if(in_array($nm, array_keys(get_object_vars($this))))
    {
      return $this->$nm;
    }
    else
    {
      return (array_key_exists($nm, $this->DATA)) ? $this->DATA[$nm] : null;
    }
  }

  /**
   * Convert the database object to an array
   *
   * @return array
   */
  public function ToArray()
  {
    $data = array();
    foreach(get_object_vars($this) as $k => $v)
    {
      $data[$k] = $v;
    }

    return array_merge($data,$this->DATA);
  }
}
