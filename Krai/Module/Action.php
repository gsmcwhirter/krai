<?php
/**
 * Krai action base class
 * @package Krai
 * @subpackage Module
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

/**
 * Action base class
 * @package Krai
 * @subpackage Module
 */
abstract class Krai_Module_Action extends Krai_Base
{
  /**
   * Holds a reference to the parent module
   *
   * @var Krai_Module
   */
  protected $_parent;

  /**
   * Flag for if the flow has been redirected
   *
   * @var boolean
   */
  protected $_redirected = false;

  /**
   * The request method for the current action
   *
   * @var string
   */
  protected $_RequestMethod;

  /**
   * Whether or not any rendering has been done yet.
   * @var boolean
   */
  private static $_RENDER_STARTED = false;

  /**
   * Constructor
   *
   * @param Krai_Module $_parent
   * @param string $_rm The request method
   * @throws Krai_Module_Exception
   */
  public function __construct(Krai_Module &$_parent, $_rm)
  {
    $this->_parent = $_parent;

    if (!in_array($_rm, array("GET","POST")))
    {
      throw new Krai_Module_Exception("Request method ".$_rm." was not understood.", Krai_Module_Exception::UnknownRequestMethod);
    }
    $this->_RequestMethod = strtoupper($_rm);

  }

  /**
   * Validate some data
   * @throws Krai_Module_Exception
   */
  public function Validate()
  { }

  /**
   * Process data
   * @throws Krai_Module_Exception
   */
  public function Process()
  { }

  /**
   * Display something
   *
   */
  public function Display()
  { }

  /**
   * Handle an error being thrown
   *
   * @param integer $_ErrorCode
   * @param string $_ErrorMsg
   */
  public function HandleError($_ErrorCode, $_ErrorMsg)
  { }

  /**
   * Render a file with or without a template
   *
   * @param string $_file
   * @param boolean $_templated
   * @throws Krai_Module_Exception_Adone
   */
  protected function Render($_file, $_templated = true)
  {
    if(!self::$_RENDER_STARTED)
    {
      self::$_RENDER_STARTED = true;
      $this->BeforeRender();
    }

    if ($_templated)
    {
      $mconf = Krai::GetConfig("CONFIG_MODULE");
      $layout = (is_string($_templated)) ? $_templated : $mconf["DEFAULT_LAYOUT"];
      if($layout)
      {
        include Krai::$LAYOUTS."/".$layout."/header.phtml";
        include Krai::$MODULES."/".$_file;
        include Krai::$LAYOUTS."/".$layout."/footer.phtml";
      }
      else
      {
        include Krai::$MODULES."/".$_file;
      }
    }
    else
    {
      include Krai::$MODULES."/".$_file;
    }

    throw new Krai_Module_Exception_Adone();
  }

  /**
   * Renders a partial file
   *
   * @param string $_file
   * @param mixed $_collection
   * @param array $_locals
   *
   */
  protected function RenderPartial($_file, $_collection = array(), array $_locals = array())
  {
    if(!self::$_RENDER_STARTED)
    {
      self::$_RENDER_STARTED = true;
      $this->BeforeRender();
    }

    $_varname = preg_replace("#([^a-zA-Z0-9_])|(\.phtml)#","",array_pop(explode("/",$_file)));
    if(is_array($_collection))
    {

      foreach($_collection as ${$_varname})
      {
        foreach($_locals as $_k => $_v)
        {
          ${preg_replace("#^_*#","",$_k)} = $_v;
        }
        include Krai::$MODULES."/".$_file;
      }
    }
    else
    {
      ${$_varname} = $_collection;
      include Krai::$MODULES."/".$_file;
    }
  }

  /**
   * Renders some text with or without a template
   *
   * @param string $_text
   * @param boolean $_templated
   * @throws Krai_Module_Exception_Adone
   */
  protected function RenderText($_text, $_templated = true)
  {
    if(!self::$_RENDER_STARTED)
    {
      self::$_RENDER_STARTED = true;
      $this->BeforeRender();
    }

    if ($_templated)
    {
      $mconf = Krai::GetConfig("CONFIG_MODULE");
      $layout = (is_string($_templated)) ? $_templated : $mconf["DEFAULT_LAYOUT"];
      if($layout)
      {
        include Krai::$LAYOUTS."/".$layout."/header.phtml";
        echo $_text;
        include Krai::$LAYOUTS."/".$layout."/footer.phtml";
      }
      else
      {
        echo $_text;
      }
    }
    else
    {
      echo $_text;
    }

    throw new Krai_Module_Exception_Adone();
  }

  /**
   * Filters to apply before the validate call
   *
   */
  public function BeforeFilters()
  { }

  /**
   * Filters to apply before any rendering takes place. If no render call is made, this is not executed.
   *
   */
  public function BeforeRender()
  { }

  /**
   * Filters to apply after the Display call
   *
   */
  public function AfterFilters()
  { }

  /**
   * Execute a redirect
   *
   * @param string $module
   * @param string $action
   * @param array $params
   * @see Krai_Module::RedirectTo()
   */
  protected function RedirectTo($module, $action = null, array $params = array())
  {
    $this->_parent->RedirectTo($module, $action, $params);
  }

}
