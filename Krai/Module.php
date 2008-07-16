<?php
/**
 * Krai module base class
 * @package Krai
 * @subpackage Module
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Module/Action.php",
  Krai::$FRAMEWORK."/Module/Exception.php",
  Krai::$FRAMEWORK."/Module/Exception/Adone.php",
  Krai::$FRAMEWORK."/Module/Exception/Mdone.php"
);

/**
 * Module base class
 *
 * This class is the base of the modules in any application. It controls execution
 * of {@link Krai_Action} instances, and provides filtering and redirection abilities.
 *
 * @package Krai
 * @subpackage Module
 */
abstract class Krai_Module extends Krai_Base
{

  /**
   * Perform an action from this module
   *
   * This function executes an action which belongs to the module instance.
   *
   * @param string $action The name of the action to execute
   * @param string $requestmethod The method of the request (GET or POST)
   * @throws Krai_Module_Exception_Mdone
   */
  public function DoAction($action, $requestmethod)
  {
    $class = get_class($this);
    $fsc = null;

    while($class != "Krai_Module" && substr($class, -6) == "Module" && is_null($fsc))
    {
      $class2 = Krai::$INFLECTOR->Camel2Underscore($class);
      $act = Krai::$INFLECTOR->Camel2Underscore($action);
      $f = Krai::$MODULES."/".substr($class2, 0, -7).".module/actions/".$act.".action.php";
      if(file_exists($f))
      {
        $fsc = Krai::$INFLECTOR->Underscore2Camel($class2)."_".Krai::$INFLECTOR->Underscore2Camel($action."_action");
      }
      else
      {
        $class = get_parent_class($class);
      }
    }

    if(is_null($fsc))
    {
      throw new Krai_Module_Exception("Unable to find requested action ".$action." (".$act." : ".$f.").", Krai_Module_Exception::ActionNotFound);
    }
    else
    {
      Krai_Base::$ROUTER->Load($fsc);
    }


    try
    {
      $this->BeforeFilters();
      $actioninst = new $fsc($this, $requestmethod);

      try
      {
        $actioninst->BeforeFilters();
        $actioninst->Validate();
        $actioninst->Process();
      }
      catch(Krai_Module_Exception $e)
      {
        $actioninst->HandleError($e->getCode(), $e->getMessage());
      }

      $actioninst->Display();
      $actioninst->AfterFilters();
      $this->AfterFilters();

      throw new Krai_Module_Exception_Mdone();
    }
    catch(Krai_Module_Exception_Adone $e)
    {
      $actioninst->AfterFilters();
      $this->AfterFilters();

      throw new Krai_Module_Exception_Mdone();
    }
  }

  /**
   * Executes arbitrary commands before the action instance is created.
   *
   * This function provides the ability to interject operations such as validating
   * logins or initializing tools before the action is executed.
   *
   */
  protected function BeforeFilters()
  {}

  /**
   * Executes arbitrary commands after the action instance has been completed.
   *
   * This function provides the ability to tack on operations to the end of the
   * execution of an action for example, for the purpose of cleaning up file handles
   *
   */
  protected function AfterFilters()
  {}

  /**
   * Execute a redirect
   *
   * This function executes a redirect via a Location header. It can redirect to
   * another module/action/params target as generated by {@link Krai_Router::UrlFor()},
   * or to an arbitrary URL by passing that url as the only parameter.
   *
   * @param string $module The module or URL to redirect to
   * @param string $action The action to redirect to
   * @param array $params The parameters for generating the url
   * @throws Krai_Module_Exception_Adone
   */
  public function RedirectTo($module, $action = null, array $params = array())
  {
    if(is_null($action))
    {
      header("Location: ".$module);
    }
    else
    {
      header("Location: ".self::$ROUTER->UrlFor($module, $action, $params, array(), false));
    }

    throw new Krai_Module_Exception_Adone();
  }

  /**
   * Execute a redirect behind the scenes
   *
   * This function executes a redirect to another module/action pair behind the
   * scenes. The browser is not redirected, just the script execution.
   *
   * @param string $module The module to redirect to
   * @param string $action The action to execute
   * @param array $params The parameters for the request
   * @throws Krai_Module_Exception_Adone
   */
  public function RedirectSilent($module, $action = null, array $params = array())
  {
    self::$ROUTER->ExecuteRoute($module, $action, $params);

    throw new Krai_Module_Exception_Adone();
  }

}
