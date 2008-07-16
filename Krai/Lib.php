<?php
/**
 * Krai functions library
 *
 * This file includes the required
 * bundled libraries, including {@link Krai_Lib_Inflector}, {@link Nakor},
 * and {@link Spyc}. The autoloader that was formerly included has been deprecated
 * in order to give more flexibility to application writers.
 *
 * @package Krai
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Lib/Inflector.php",
  Krai::$FRAMEWORK."/Lib/Nakor.php",
  Krai::$FRAMEWORK."/Lib/Spyc.php"
);

/**
 * PHP Magic autoload function for classes.  Set to only work on module, action, and model files.
 *
 * This function is a php magic function for trying to load classes when they are
 * not currently defined.
 *
 * @param string $class Name of the module to be loaded.
 * @return boolean Return the result of the Uses call or false if the requested class was not a module
 */
/*function __autoload($class)
{

  if(!Krai::$INFLECTOR instanceOf Krai_Lib_Inflector)
  {
    Krai::$INFLECTOR = new Krai_Lib_Inflector();
  }

  $class = Krai::$INFLECTOR->Camel2Underscore($class);
  if(substr($class,-6) == "module")
  {
    $f = Krai::$MODULES."/".substr($class,0,-7).".module/".substr($class,0,-7).".module.php";
    if(Krai::Uses(true, $f))
    {
      return true;
    }
    else
    {
      $e = new Exception("Autoload failed for file ".$f);
      include Krai::$FRAMEWORK."/Exception.phtml";
      exit(0);
    }
  }
  elseif(substr($class, -6) == "action")
  {
    list($mod, $act) = explode("module", $class, 2);
    $f = Krai::$MODULES."/".substr($mod,0,-1).".module/actions/".substr($act,1,-7).".action.php";
    if(Krai::Uses(true, $f))
    {
      return true;
    }
    else
    {
      $e = new Exception("Autoload failed for file ".$f);
      include Krai::$FRAMEWORK."/Exception.phtml";
      exit(0);
    }
  }
  else
  {
    $e = new Exception("Autoload failed for class ".$class);
    include Krai::$FRAMEWORK."/Exception.phtml";
    exit(0);
  }
}*/
