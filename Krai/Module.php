<?php
/**
 * Krai module base class
 * @package Krai
 * @subpackage Module
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Module/Action.php",
  Krai::$FRAMEWORK."/Module/Exception.php",
  Krai::$FRAMEWORK."/Module/Exception/Adone.php",
  Krai::$FRAMEWORK."/Module/Exception/Mdone.php"
);

/**
 * Module base class
 * @package Krai
 * @subpackage Module
 */
abstract class Krai_Module extends Krai_Base
{

  /**
   * Perform an action from this module
   *
   * @param string $action
   * @param string $requestmethod
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
   */
  protected function BeforeFilters()
  {}

  /**
   * Executes arbitrary commands after the action instance has been completed.
   *
   */
  protected function AfterFilters()
  {}

  /**
   * Execute a redirect
   *
   * @param string $module
   * @param string $action
   * @param array $params
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
   * @param string $module
   * @param string $action
   * @param array $params
   * @throws Krai_Module_Exception_Adone
   */
  public function RedirectSilent($module, $action = null, array $params = array())
  {
    self::$ROUTER->ExecuteRoute($module, $action, $params);

    throw new Krai_Module_Exception_Adone();
  }

}
