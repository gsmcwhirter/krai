<?php
/**
 * Krai router class
 * @package Krai
 * @subpackage Router
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Router/Route.php",
  Krai::$FRAMEWORK."/Router/Exception.php"
);

/**
 * The framework router
 *
 * This class controls parsing request uris into the execution of module actions.
 * It also generates uris from module and action data that will get back to that
 * module and action when re-parsed. It also has functionality to load files in
 * which live certain modules and actions.
 *
 * Implements the Singleton pattern via {@link Krai_Router::Instance()}.
 *
 * @package Krai
 * @subpackage Router
 */
final class Krai_Router
{

  /**
   * Holds the singleton instance
   *
   * This holds the single instance of the routing class.
   *
   * @var Krai_Router
   */
  private static $_instance = null;

  /**
   * Holds the array of defined routes sorted by complexity
   *
   * This is an array of arrays of {@link Krai_Router_Route} instances. It is in
   * levels of complexity to make finding a matching route (perhaps) easier.
   *
   * @var array
   */
  private $_routemap = array();

  /**
   * Holds the array of defined routes in entry order
   *
   * This is an array of {@link Krai_Router_Route} instances sorted in the order
   * they were parsed out of the routes configuration file. It is used for reconstructing
   * routes from module and action data.
   *
   * @var array
   */
  private $_reconstrmap = array();

  /**
   * Flag for whether or not routing has already occurred.
   *
   * This is a flag indicating whether or not {@link Krai_Router::DoRoute()} has
   * been run yet.
   *
   * @var boolean
   */
  private $_routed = false;

  /**
   * Constructor - private to implement singleton pattern
   *
   * This function parses the routes configuration file and stores the route objects.
   *
   * @param string $kvfurl
   *
   */
  private function __construct()
  {
    /* Load the route map */
    $lines = file(Krai::$INCLUDES."/configs/routes.config");
    foreach($lines as $line)
    {
      $line = trim($line);
      if(substr($line,0,1) == "#" || empty($line))
      {
        continue;
      }

      $t = explode("-->", trim($line), 2);
      $pattern = trim($t[0]);
      $actmap = trim((array_key_exists(1, $t)) ? $t[1] : "");
      $pattern = trim(preg_replace(array("#^[/]*#","#[/]*$#"),array("",""),$pattern));
      $patparts = (empty($pattern)) ? array() : explode("/", $pattern);
      $actparts = (empty($actmap)) ? array() : explode(",", $actmap);
      $forces = array();

      foreach($actparts as $act)
      {
        list($k,$v) = explode("=", $act);
        $k = trim($k);
        if(substr($k,0,1) == ":")
        {
          $forces[substr($k,1)] = trim($v);
        }
      }

      if(!array_key_exists(count($patparts), $this->_routemap))
      {
        $this->_routemap[count($patparts)] = array();
      }

      $this->_routemap[count($patparts)][] = new Krai_Router_Route($patparts,$forces);
      $this->_reconstrmap[] = new Krai_Router_Route($patparts, $forces);
    }

    $this->_baseuri = Krai::GetConfig("BASEURI") == "" ? "" : "/".Krai::GetConfig("BASEURI");
    Krai::WriteLog($this->_baseuri, Krai::LOG_DEBUG);
  }

  /**
   * Singleton pattern constructor / retreiver
   *
   * This function allows the retrieval of the singleton instance of the class.
   *
   * @return Krai_Router The router instance
   */
  public static function &Instance()
  {

    if(!(self::$_instance instanceOf Krai_Router))
    {
      $c = "Krai_Router";
      self::$_instance = new $c();
    }
    return self::$_instance;
  }

  /**
   * Execute a route
   *
   * This function executes a route based on the parsing of the request parameter.
   * It can only be called once, and after that will throw a Krai_Router_Exception.
   *
   * @param string $request The requested uri
   * @throws Krai_Router_Exception
   */
  public function DoRoute($request)
  {
    if(!$this->_routed)
    {
      $request = preg_replace(array("#^[/]*#","#[/]*$#"),array("",""), $request);
      //$request = preg_replace("#\.html$#","", $request);
      $rparts = (empty($request)) ? array() : explode("/", $request);
      if(array_key_exists(count($rparts), $this->_routemap))
      {
        $found = null;
        foreach($this->_routemap[count($rparts)] as $route)
        {
          $t = $route->Matches($rparts);
          if($t!==false)
          {
            $found = $t;
            break;
          }
        }

        if(is_null($found))
        {
          throw new Krai_Router_Exception("Unable to find a route matching the request.", Krai_Router_Exception::NoRouteFound);
        }
        else
        {
          $this->_routed = true;
          $this->ExecuteRoute($found["module"], $found["action"], $found["params"]);
        }
      }
      else
      {
        throw new Krai_Router_Exception("Unable to find a route with the proper number of arguments.", Krai_Router_Exception::NoRouteFound);
      }
    }
    else
    {
      throw new Krai_Router_Exception("Routing has already been performed for this request.", Krai_Router_Exception::RoutingPerformed);
    }
  }

  /**
   * Actually execute a route
   *
   * This function implements the actual execution of a route by instantiating
   * the required module and calling the module's {@link Krai_Module::DoAction()}
   * method.
   *
   * @param string $_module The name of the module to instantiate
   * @param string $_action The name of the action to execute
   * @param array $_params The parameters of the request
   * @throws Krai_Router_Exception
   */
  public function ExecuteRoute($_module, $_action, array $_params = array())
  {
    if(!is_null($_module) && !empty($_action))
    {
      Krai_Base::$PARAMS = array_merge(Krai_Base::$PARAMS, $_params);
      $t = Krai::$INFLECTOR->Underscore2Camel($_module."_module");
      $inst = new $t();
      $inst->DoAction($_action, $_SERVER["REQUEST_METHOD"]);
    }
    else
    {
      throw new Krai_Router_Exception("Matching route did not yield a module to which to route.", Krai_Router_Exception::NoRouteAction);
    }
  }

  /**
   * Generate the URL for a certain module and action
   *
   * This function generates a uri representing a certain combination of module,
   * action, and parameters which, when parsed, would execute the same.
   *
   * @param string $_module The name of the module
   * @param string $_action The name of the action
   * @param array $_params An array of parameters
   * @param boolean $_forlink Whether or not to encode the uri returned for use in a link
   * @return string The uri (including BASEURI).
   */
  public function UrlFor($_module, $_action, array $_params = array(), $_forlink = true)
  {
    foreach($this->_reconstrmap as $route)
    {
      if($route->MatchUrlFor($_module, $_action, $_params))
      {
        //The route is a match now
        Krai::WriteLog("Matched Route.".serialize($route), Krai::LOG_DEBUG);
        return $this->_baseuri.$route->Reconstruct($_module, $_action, $_params, $_forlink);
      }
    }
  }

}
