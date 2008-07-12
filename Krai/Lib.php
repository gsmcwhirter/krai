<?php
/**
 * Krai functions library
 * @package Krai
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Lib/Inflector.php",
  Krai::$FRAMEWORK."/Lib/Nakor.php",
  Krai::$FRAMEWORK."/Lib/Spyc.php"
);

/**
 * PHP Magic autoload function for classes.  Set to only work on module, action, and model files.
 * @param string $class Name of the module to be loaded.
 * @return boolean Return the result of the Uses call or false if the requested class was not a module
 */
function __autoload($class)
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
}
