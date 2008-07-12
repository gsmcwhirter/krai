<?php
/**
 * Krai router class
 * @package Krai
 * @subpackage Router
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Router/Route.php",
  Krai::$FRAMEWORK."/Router/Exception.php"
);

/**
 * The framework router
 *
 * @package Krai
 * @subpackage Router
 */
final class Krai_Router
{

  /**
   * Holds the singleton instance
   *
   * @var Krai_Router
   */
  private static $_instance = null;

  /**
   * Holds the array of defined routes sorted by complexity
   *
   * @var array
   */
  private $_routemap = array();

  /**
   * Holds the array of defined routes in entry order
   * @var array
   */
  private $_reconstrmap = array();

  /**
   * Flag for whether or not routing has already occurred.
   *
   * @var boolean
   */
  private $_routed = false;

  /**
   * Constructor - private to implement singleton pattern
   * @param string $kvfurl
   *
   */
  private function __construct()//$kvfurl)
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

    /*$ruri = preg_replace("#\?.*$#", "", urldecode($_SERVER["REQUEST_URI"]));
    Krai::WriteLog($kvfurl, Krai::LOG_DEBUG );
    Krai::WriteLog($ruri, Krai::LOG_DEBUG );

    $sp = (!empty($kvfurl)) ? strpos($ruri, $kvfurl) : false;
    if($sp === false)
    {
      $t1 = $ruri;
    }
    else
    {
      $t1 = substr($ruri, 0, $sp);
    }

    $this->_baseuri = preg_replace(array("#^[/]*#","#[/]*$#"),array("",""),$t1);
    if(!empty($this->_baseuri))
    {
      $this->_baseuri = "/".$this->_baseuri;
    }*/

    $this->_baseuri = Krai::GetConfig("BASEURI") == "" ? "" : "/".Krai::GetConfig("BASEURI");
    Krai::WriteLog($this->_baseuri, Krai::LOG_DEBUG);
  }

  /**
   * Singleton pattern constructor / retreiver
   *
   * @return Krai_Router
   */
  public static function &Instance()//$kvfurl)
  {

    if(!(self::$_instance instanceOf Krai_Router))
    {
      $c = "Krai_Router";
      self::$_instance = new $c();//$kvfurl);
    }
    return self::$_instance;
  }

  /**
   * Execute a route
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
   * @param string $_module
   * @param string $_action
   * @param array $_params
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
   * @param string $_module
   * @param string $_action
   * @param array $_params
   * @return string
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


  /**
   * Returns an array of string representations of all route parsings
   *
   * @return array
   */
  public function ShowAllRoutes()
  {
    $ret = array();
    foreach($this->_reconstrmap as $routes)
    {
      foreach($routes as $route)
      {
        $ret[] = implode("/",$route->GetParts())." : ".Krai::AssocImplode(", "," => ", $route->GetFixedMap());
      }
    }
    return $ret;
  }

}