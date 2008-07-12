<?php
/**
 * A Struct type abstract class
 * @package Krai
 * @subpackage Struct
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Struct/Exception.php"
);

 /**
  * A struct type abstract class
  * @package Krai
  * @subpackage Struct
  */
abstract class Krai_Struct
{

  /**
   * Array of allowed properties of the struct
   *
   * @var array
   */
  protected $FIELDS = array();

  /**
   * Array of allowed variable of the struct
   *
   * @var array
   */
  protected $VARS = array();

  /**
   * Array of current values of the Fields
   *
   * @var array
   */
  protected $DATA = array();

  /**
   * Constructor
   *
   * @param array $new_defaults Override the allowed fields
   */
  public function __construct(array $new_defaults = array())
  {
    if (!is_array($this->FIELDS))
    {
      $this->FIELDS = array();
    }
    $this->FIELDS = array_merge($this->FIELDS, array_intersect_key($new_defaults, $this->FIELDS));
    foreach($this->FIELDS as $f => $d)
    {
      $this->DATA[$f] = $d;
    }
  }

  /**
   *
   * PHP magic function for getting a property value
   *
   * @param mixed $m
   * @return mixed
   * @throws Krai_Struct_Exception
   */
  public function __get($m)
  {
    if($this->VarAllowed($m))
    {
      return $this->DATA[$m];
    }
    else
    {
      throw new Krai_Struct_Exception("Variable $m does not exist in the struct.");
    }
  }

  /**
   * PHP magic function for setting a property value
   *
   * @param mixed $m
   * @param mixed $v
   * @throws Krai_Struct_Exception
   */
  public function __set($m, $v)
  {
    if($this->VarAllowed($m) && gettype($v) == gettype($this->$m))
    {
      $this->DATA[$m] = $v;
    }
    else
    {
      throw new Krai_Struct_Exception("Setting variable $m to $v failed. Variable name or type not allowed.");
    }
  }

  /**
   * PHP magic function for calling a method
   *
   * @param mixed $m
   * @param mixed $a
   * @throws Krai_Struct_Exception
   */
  public function __call($m, $a)
  {
    throw new Krai_Struct_Exception("You cannot call a method on a struct.");
  }

  /**
   * PHP magic function for unsetting a property
   *
   * @param mixed $m
   * @throws Krai_Struct_Exception
   */
  public function __unset($m)
  {
    throw new Krai_Struct_Exception("You cannot unset a struct variable,");
  }

  /**
   * Determine whether a property is allowed to be get / set
   *
   * @param mixed $m
   * @return boolean
   */
  protected function VarAllowed($m)
  {
    return((in_array($m, array_keys($this->FIELDS)) || in_array($m, $this->VARS)) ? true : false );
  }

}
