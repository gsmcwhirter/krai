<?php
/**
 * A Struct type abstract class
 *
 * This file contains the Krai_Struct class, which is a sort of approximation
 * of the struct types of other languages.
 *
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
  *
  * This class provides a sort of approximation of a struct type from some other
  * languages. The {@link Krai_Struct::$FIELDS} variable holds the names and
  * default values of the allowed properties. The default values are also used to
  * determine what type the values of those properties must have.
  *
  * The class uses the PHP overloading functions to control the public behavior.
  *
  * @package Krai
  * @subpackage Struct
  */
abstract class Krai_Struct
{

  /**
   * Array of allowed properties of the struct
   *
   * This variable holds the names and
   * default values of the allowed properties. The default values are also used to
   * determine what type the values of those properties must have.
   *
   * @var array
   */
  protected $FIELDS = array();

  /**
   * Array of current values of the Fields
   *
   * This variable holds the data that currently resides in each of the allowed
   * properties.
   *
   * @var array
   */
  protected $DATA = array();

  /**
   * Constructor
   *
   * This function initializes the struct instance. It accepts overrides of the
   * default values, in lieu of setting those values after initialization, for
   * convenience.
   *
   * @param array $new_defaults Override the default values of the allowed fields
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
   * Returns the current value of a property, or throws a Krai_Struct_Exception
   * if that property is not valid.
   *
   * @param mixed $m The name of the property of which to get the value
   * @return mixed The value of the property
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
    return (in_array($m, array_keys($this->FIELDS))) ? true : false;
  }

}
