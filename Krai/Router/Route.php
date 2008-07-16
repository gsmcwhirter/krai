<?php
/**
 * Krai route instance
 * @package Krai
 * @subpackage Router
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * A route instance
 *
 * @package Krai
 * @subpackage Router
 */
class Krai_Router_Route
{

  /**
   * Holds the parts of the pattern for this route
   *
   * @var array
   */
  private $_parts = array();

  /**
   * Holds the variable overrides for this route
   *
   * @var array
   */
  private $_forcemap = array();

  /**
   * Holds the variables that are required for reconstruct
   *
   * @var array
   *
   */
  private $_reconstruct_requires = array();

  /**
   * Constructor
   *
   * @param array $parts
   * @param array $forcemap
   */
  public function __construct(array $parts, array $forcemap)
  {
    foreach($parts as $part)
    {
      if(preg_match("#^:([a-zA-Z_0-9]*)$#", $part))
      {
        $part = "*:".$part;
      }

      $this->_parts[] = $part;
    }
    $this->_forcemap = $forcemap;


    foreach($this->_parts as $part)
    {
      if(substr($part, 0, 3) == "*::")
      {
        $this->_reconstruct_requires[] = substr($part, 3);
      }
      else
      {
        continue;
      }
    }

    sort($this->_reconstruct_requires);
  }

  /**
   * Determine whether or not a split request fits the pattern
   *
   * @param array $str_parts
   * @return mixed Array of parameters or false
   */
  public function Matches(array $str_parts)
  {
    $max = count($this->_parts);
    if($max != count($str_parts))
    {
      return false;
    }

    $str_parts = array_values($str_parts);
    $retvars = array("module" => null,"action" => "index", "params" => array());

    for($i = 0; $i < $max; $i++)
    {
      if(substr($this->_parts[$i],0,3) == "*::")
      {
        if(in_array(substr($this->_parts[$i],3), array("module","action")))
        {
          $retvars[substr($this->_parts[$i],3)] = $str_parts[$i];
        }
        else
        {
          $retvars["params"][substr($this->_parts[$i],3)] = $str_parts[$i];
        }
      }
      elseif($this->_parts[$i] != $str_parts[$i])
      {
        return false;
      }
    }

    foreach($this->_forcemap as $forcekey => $forceval)
    {
      if(in_array($forcekey, array("module","action")))
      {
        $retvars[$forcekey] = $forceval;
      }
      else
      {
        $retvars["params"][$forcekey] = $forceval;
      }
    }

    return $retvars;
  }

  public function MatchUrlFor($_module, $_action, array $_params)
  {
    Krai::WriteLog("Testing Route.".serialize($this), Krai::LOG_DEBUG);
    $_module = trim($_module);
    $_action = trim($_action);

    //is the module present?
    if(!$_module)
    {
      return false;
    }
    //is the action present?
    elseif(!$_action)
    {
      return false;
    }
    //see if the module is correct
    elseif(array_key_exists("module",$this->_forcemap) && $this->_forcemap["module"] != $_module)
    {
      Krai::WriteLog("Route Not Matched because of module. Wanted ".$this->_forcemap["module"]." and got ".$_module, Krai::LOG_DEBUG);
      return false;
    }
    //see if the action is correct
    elseif(array_key_exists("action",$this->_forcemap) && $this->_forcemap["action"] != $_action)
    {
      Krai::WriteLog("Route Not Matched because of action. Wanted ".$this->_forcemap["action"]." and got ".$_action, Krai::LOG_DEBUG);
      return false;
    }
    //see if everything else is correct
    else
    {
      //Things that are in the forcemap that are not in the params
      $diff_forcemap_to_params = array_diff_assoc($this->_forcemap, array_merge(array("module" => $_module, "action" => $_action),$_params));

      //there was something in the forcemap that was not in the params
      if(count($diff_forcemap_to_params) > 0)
      {
        Krai::WriteLog("Route did not match forcemaps. ".serialize($diff_forcemap_to_params), Krai::LOG_DEBUG);
        return false;
      }

      //Things that are in the params that are not in the forcemap
      $diff_params_to_forcemap = array_diff_assoc($_params, $this->_forcemap);

      //keys required for reconstuct which are not in the parameters provided
      $diff_reconstruct_requires_to_diff_params_to_forcemap_keys = array_diff($this->_reconstruct_requires, array_merge(array("module","action"),array_keys($diff_params_to_forcemap)));

      //there was something required not found
      if(count($diff_reconstruct_requires_to_diff_params_to_forcemap_keys) > 0)
      {
        Krai::WriteLog("Route did not match reconstruction reqs. ".serialize($diff_params_to_forcemap)." & ".serialize($diff_reconstruct_requires_to_diff_params_to_forcemap_keys), Krai::LOG_DEBUG);
        return false;
      }

      return true;
    }
  }

  /**
   * Reconstruct the uri for this route
   *
   * @param string $_module The module of the route
   * @param string $_action The action of the route
   * @param array $_params The query parameters
   * @return string The uri
   */
  public function Reconstruct($_module, $_action, array $_params = array(), $_forlink = true)
  {
    $str = "";
    $m = count($this->_parts);
    for($_i = 0; $_i < $m; $_i++)
    {
      $part = $this->_parts[$_i];
      if(substr($part, 0,3) == "*::")
      {
        $key = substr($part, 3);
        if($key == "module")
        {
          $str .= "/".$_module;
        }
        elseif($key == "action")
        {
          $str .= "/".$_action;
        }
        else
        {
          $str .= "/".$_params[$key];
          unset($_params[$key]);
        }
      }
      else
      {
        $str .= "/".$part;
      }
    }

    foreach($this->_forcemap as $fmk => $fmv)
    {
      if(array_key_exists($fmk, $_params) && $fmv == $_params[$fmk])
      {
        unset($_params[$fmk]);
      }
    }

    return $str."/?".Krai::AssocImplode(($_forlink) ? "&amp;" : "&", "=", $_params);
  }

}
