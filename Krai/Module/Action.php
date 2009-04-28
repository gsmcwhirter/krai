<?php
/**
 * Krai action base class
 *
 * This file holds the class which is the base of any module action.
 *
 * @package Krai
 * @subpackage Module
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Action base class
 *
 * This class is the basis for every module action in an application. It provides
 * filtering functionality, as well as redirection, rendering, and error handling.
 *
 * @package Krai
 * @subpackage Module
 */
abstract class Krai_Module_Action extends Krai
{
  /**
   * Holds a reference to the parent module
   *
   * This variable holds a reference to the parent module of the action. This
   * allows actions to be tied to a heirarchy of module classes instead of just one
   * from which it inherits. It also allows for data separation between the module
   * and the action.
   *
   * @var Krai_Module
   */
  protected $_parent;

  /**
   * The request method for the current action
   *
   * This variable holds the request method which generated the call to the current
   * action. It should be one of "GET" or "POST", as supplied by {@link Krai_Module::DoAction()}.
   *
   * @var string
   * @deprecated
   */
  protected $_RequestMethod;

  /**
   * Whether or not any rendering has been done yet.
   *
   * This variable is a flag for whether or not rendering has been started by any
   * action. This allows for errors to be thrown when attempting to render a
   * layout more than once.
   *
   * @var boolean
   */
  private static $_RENDER_STARTED = false;

  /**
   * Constructor
   *
   * This function initializes the action parameters. It does not control execution
   * other than that. Execution is managed within {@link Krai_Module::DoAction()}.
   *
   * @param Krai_Module $_parent The parent module of the action
   * @param string $_rm The request method [DEPRECATED]
   * @throws Krai_Module_Exception
   */
  public function __construct(Krai_Module &$_parent, $_rm = null)
  {
    $this->_parent = $_parent;

    /*if (!in_array($_rm, array("GET","POST")))
    {
      throw new Krai_Module_Exception("Request method ".$_rm." was not understood.", Krai_Module_Exception::UnknownRequestMethod);
    }
    $this->_RequestMethod = strtoupper($_rm);
	*/
	
	$this->_RequestMethod = self::$REQUEST->RequestMethod();

  }

  /**
   * Validate some data
   *
   * This is the second function of an action called, after the
   * {@link Krai_Module_Action::BeforeFilters()} in a standard application flow.
   * The intention is to validate input within this method and save it to instance
   * variables to be used later in the {@link Krai_Module_Action::Process()} and
   * {@link Krai_Module_Action::Display()} methods. If an error is found, a
   * {@link Krai_Module_Exception} should be thrown.
   *
   * @throws Krai_Module_Exception
   */
  public function Validate()
  { }

  /**
   * Process data
   *
   * This is the third function of an action called, after {@link Krai_Module_Action::BeforeFilters()}
   * and {@link Krai_Module_Action::Validate()} in a standard application flow.
   * The intention is to process validated input within this method and record
   * any messages or errors to be used later in the {@link Krai_Module_Action::Display()}
   * method. If an error is found, a {@link Krai_Module_Exception} should be thrown.
   *
   * @throws Krai_Module_Exception
   */
  public function Process()
  { }

  /**
   * Display something
   *
   * This is the fourth function of an action called, after {@link Krai_Module_Action::BeforeFilters()},
   * {@link Krai_Module_Action::Validate()}, and {@link Krai_Module_Action::Process()}
   * in a standard application flow. The intention is to implement display logic
   * to handle the actions of the Validation and Processing.
   *
   */
  public function Display()
  { }

  /**
   * Handle an error being thrown
   *
   * This function handles the {@link Krai_Module_Exception}s thrown by the
   * {@link Krai_Module_Action::Validate()} and {@link Krai_Module_Action::Process()}
   * methods. By default it is implemented to call {@link Krai::Error()} with
   * the $_ErrorMsg parameter.
   *
   * @param integer $_ErrorCode
   * @param string $_ErrorMsg
   */
  public function HandleError($_ErrorCode, $_ErrorMsg)
  {
    self::Error($_ErrorMsg);
  }

  /**
   * Render a file with or without a template
   *
   * This function is responsible for rendering a view file, optionally with a
   * template. It should only be called if neither it nor {@link Krai_Module_Action::RenderText()}
   * was previously called in the application flow. Before rendering the file,
   * it passes off to {@link Krai_Module_Action::BeforeRender()}. It throws a
   * Krai_Module_Exception_Adone, so it generally will not return to the action
   * application flow.
   *
   * The filename passed with be prefixed with {@link Krai::$MODULES} and verified
   * to be within the {@link Krai::$APPDIR} directory. Additionally, the name of
   * the layout file (whether passed or default) will be prefixed with {@link Krai::$LAYOUTS}
   * and also verified to live within the application root {@link Krai::$APPDIR}.
   *
   * @param string $_file The name of the file to be rendered
   * @param boolean $_templated The name of the layout to use or true to use the
   * default layout or false to use no layout
   * @param boolean $_cached Whether to pass off the content to the cacher afterwards
   * or not
   * @throws Krai_Module_Exception_Adone
   * @throws Krai_Module_Exception
   */
  protected function Render($_file, $_templated = true, $_cached = false)
  {

    if(!self::$_RENDER_STARTED)
    {
      self::$_RENDER_STARTED = true;
      $this->BeforeRender();
    }

    $_file = realpath(Krai::$MODULES."/".$_file);
    if(strstr($_file, realpath(Krai::$APPDIR)) != 0)
    {
      throw new Krai_Module_Exception("Invalid path to view file. File must be within application root.", Krai_Module_Exception::FilePathError);
    }

	if($_cached)
	{ob_start();}

	if ($_templated)
    {
		$mconf = Krai::GetConfig("CONFIG_MODULE");
		$layout = (is_string($_templated)) ? $_templated : $mconf["DEFAULT_LAYOUT"];

		if($layout)
		{
		  $layout = realpath(Krai::$LAYOUTS."/".$layout);
		  if(strstr($layout, realpath(Krai::$APPDIR)) != 0)
		  {
			throw new Krai_Module_Exception("Invalid path to layout. Layout file must be within application root.", Krai_Module_Exception::FilePathError);
		  }

		  include $layout."/header.phtml";
		  include $_file;
		  include $layout."/footer.phtml";
		}
		else
		{
		  include $_file;
		}
    }
    else
    {
      include $_file;
    }

	if($_cached)
	{
		$contents = ob_get_contents();
		ob_end_flush();
		Krai::CacheFile($contents);
	}

    $this->AfterRender();

    throw new Krai_Module_Exception_Adone(Krai_Module_Exception_Adone::Rendered);
  }

	/**
	 *{@see Krai_Module_Action::Render()}
	 */
  protected function RenderCached($_file, $_templated = true)
  {
	$this->Render($_file, $_templated, true);
  }

  /**
   * Renders a partial file
   *
   * This function provides the ability to render a "partial" within another render.
   * It accepts some data and assigns that to a local variable based on the name
   * of the partial file that will be available within the scope of the partial
   * file. If an array of data is passed, it will include the partial file once
   * for each element of the array.
   *
   * The filename passed with be prefixed with {@link Krai::$MODULES} and verified
   * to be within the {@link Krai::$APPDIR} directory.
   *
   * @param string $_file The name of the partial file
   * @param mixed $_collection The data for the partial file
   * @param array $_locals Some other local variables that will be available to
   * the partial file in the form of varname => value pairs
   *
   */
  protected function RenderPartial($_file, $_collection = array(), array $_locals = array())
  {
    if(!self::$_RENDER_STARTED)
    {
      self::$_RENDER_STARTED = true;
      $this->BeforeRender();
    }

    $_file = realpath(Krai::$MODULES."/".$_file);
    if(strstr($_file, realpath(Krai::$APPDIR)) != 0)
    {
      throw new Krai_Module_Exception("Invalid path to view file. File must be within application root.", Krai_Module_Exception::FilePathError);
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
        include $_file;
      }
    }
    else
    {
      ${$_varname} = $_collection;
      include $_file;
    }
  }

  /**
   * Renders some text with or without a template
   *
   * This function is responsible for rendering some text (e.g. JSON), optionally
   * with a template. It should only be called if neither it nor {@link Krai_Module_Action::Render()}
   * was previously called in the application flow. Before rendering the file,
   * it passes off to {@link Krai_Module_Action::BeforeRender()}. It throws a
   * Krai_Module_Exception_Adone, so it generally will not return to the action
   * application flow.
   *
   * The name of the layout file (whether passed or default) will be prefixed with
   * {@link Krai::$LAYOUTS} and verified to live within the application root {@link Krai::$APPDIR}.
   *
   * @param string $_text The text to be rendered
   * @param boolean $_templated The name of the layout or true to use the default
   * layout or false to use no layout
   * @param boolean $_cached Whether to pass off the content to the cacher afterwards
   * or not
   * @throws Krai_Module_Exception_Adone
   * @throws Krai_Module_Exception
   */
  protected function RenderText($_text, $_templated = true, $_cached = false)
  {
    if(!self::$_RENDER_STARTED)
    {
      self::$_RENDER_STARTED = true;
      $this->BeforeRender();
    }

	if($_cached)
	{ob_start();}

	if ($_templated)
    {
      $mconf = Krai::GetConfig("CONFIG_MODULE");
      $layout = (is_string($_templated)) ? $_templated : $mconf["DEFAULT_LAYOUT"];
      if($layout)
      {
        $layout = realpath(Krai::$LAYOUTS."/".$layout);
        if(!strstr($layout, realpath(Krai::$APPDIR)) != 0)
        {
          throw new Krai_Module_Exception("Invalid path to layout. Layout file must be within application root.", Krai_Module_Exception::FilePathError);
        }

        include $layout."/header.phtml";
        echo $_text;
        include $layout."/footer.phtml";
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

	if($_cached)
	{
		$contents = ob_get_contents();
		ob_end_flush();
		Krai::CacheFile($contents);
	}

    $this->AfterRender();

    throw new Krai_Module_Exception_Adone(Krai_Module_Exception_Adone::Rendered);
  }

	/**
	 *{@see Krai_Module_Action::RenderText()}
	 */
  protected function RenderTextCached($_text, $_templated = true)
  {
	$this->RenderText($_text, $_templated, true);
  }

  /**
   * Filters to apply before the validate call
   *
   * This function provides the ability for things to happen before the call to
   * {@link Krai_Module_Action::Validate()}. It should always call
   * <code>
   * parent::BeforeFilters();
   * </code>
   * in order to allow stacking.
   *
   */
  public function BeforeFilters()
  { }

  /**
   * Filters to apply before any rendering takes place. If no render call is made,
   * this is not executed.
   *
   * This function provides the ability for things to happen before a rendering
   * takes place via either {@link Krai_Module_Action::Render()} or {@link Krai_Module_Action::RenderText()}
   * It should always call
   * <code>
   * parent::BeforeRender();
   * </code>
   * in order to allow stacking.
   *
   */
  public function BeforeRender()
  { }

  /**
   * Filters to apply after any rendering takes place. If no render call is made,
   * this is not executed.
   *
   * This function provides the ability for things to happen after a rendering
   * takes place via either {@link Krai_Module_Action::Render()} or {@link Krai_Module_Action::RenderText()}
   * It should always call
   * <code>
   * parent::AfterRender();
   * </code>
   * in order to allow stacking.
   *
   */
  public function AfterRender()
  { }

  /**
   * Filters to apply after the Display call
   *
   * This function provides the ability for things to happen after the call to
   * {@link Krai_Module_Action::Display()}. It should always call
   * <code>
   * parent::AfterFilters();
   * </code>
   * in order to allow stacking.
   *
   */
  public function AfterFilters()
  { }

  /**
   * Execute a redirect
   *
   * This function executes a redirect by means of the {@link Krai_Module::RedirectTo()}
   * function. See that function for details.
   *
   * @param string $module The name of the module or the url
   * @param string $action The name of the action or null to use a url
   * @param array $params The array of parameters
   * @see Krai_Module::RedirectTo()
   */
  protected function RedirectTo($module, $action = null, array $params = array())
  {
    $this->_parent->RedirectTo($module, $action, $params);
  }

  /**
   * Execute a redirect behind the scenes
   *
   * This function executes a redirect by means of the {@link Krai_Module::RedirectSilent()}
   * function. See that function for details.
   *
   * @param string $module The name of the module
   * @param string $action The name of the action
   * @param array $params The array of parameters
   * @see Krai_Module::RedirectSilent()
   */
  protected function RedirectSilent($_module, $_action, array $_params = array())
  {
    $this->_parent->RedirectSilent($_module, $_action, $_params);
  }

}
