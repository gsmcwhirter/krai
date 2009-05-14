<?php
/**
 * A class representing the incoming request
 *
 * This contains the code for a request class, representing the incoming request.
 *
 * @package Krai
 * @subpackage Request
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Request/Exception.php",
  Krai::$FRAMEWORK."/Lib/Nakor.php"
);

/**
 * A class representing the incoming request
 *
 * @package Krai
 * @subpackage Request
 */
class Krai_Request
{

	/**
	 * Holds the $_GET variables
	 * @var array
	 */
	private $_get;

	/**
	 * Holds the $_POST variables
	 * @var array
	 */
	private $_post;

	/**
	 * Holds other variables
	 * @var array
	 */
	private $_params;

	/**
	 * Holds the request uri
	 * @var string
	 */
	private $_uri;

	/**
	 * Whether the params have been set or not.
	 * @var boolean
	 */
	private $_params_set = false;

	/**
	 * The contents of $_SERVER
	 * @var array
	 *
	 */
	private $_server;

	/**
	 * Holds an input scrubbing instance
	 * @var Nakor
	 */
	protected static $_NAKOR;

	/**
	 * Whether the input scrubber has been initialized or not.
	 * @var boolean
	 */
	private static $_INIT = false;

	/**
	 * Initializes the input scrubber mechanism.
	 *
	 */
	public static function Init()
	{
		if(self::$_INIT)
		{
			throw new Krai_Request_Exception("Krai_Request has already been initialized.");
		}

		self::$_INIT = true;

		self::$_NAKOR = new Nakor();
	}

	/**
	 * The constuctor
	 * @param array $_get The $_GET contents
	 * @param array $_post The $_POST contents
	 * @param array $_server The $_SERVER contents
	 * @param string $_referer The request referer
	 * @param string $_uri The request URI
	 * @param string $_method The request method
	 */
	public function __construct(array $_get, array $_post, array $_server, $_uri)
	{
		$this->_get = $_get;
		$this->_post = $_post;
		$this->_server = $_server;
		$this->_uri = $_uri;
	}

	public function Clean($value)
	{
		return self::$_NAKOR->CleanInput($value);
	}

	/**
	 * Sets the parameters for the request from the router component.
	 *	@param array $_params The parameters to set
	 *	@return null
	 */
	public function SetParams(array $_params)
	{
		if ($this->_params_set)
		{
			throw new Krai_Request_Exception("You cannot set the request parameters more than once.");
		}

		$this->_params = $_params;
		$this->_params_set = true;
	}

	/**
	 * Retrieves a value from the $_GET variables
	 * @param string $name The name of the variable key to retrieve
	 * @return mixed The variable value
	 */
	public function Get($name)
	{
		return $this->Clean($this->GetRaw($name));
	}

	/**
	 * Retrieves a value from the $_GET variables
	 * @param string $name The name of the variable key to retrieve
	 * @return mixed The variable value
	 */
	public function GetRaw($name)
	{
		if (array_key_exists($name, $this->_get))
		{
			return $this->_get[$name];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Retrieves a value from the $_POST variables
	 * @param string $name The name of the variable key to retrieve
	 * @return mixed The variable value
	 */
	public function Post($name)
	{
		return $this->Clean($this->PostRaw($name));
	}

	/**
	 * Retrieves a value from the $_POST variables
	 * @param string $name The name of the variable key to retrieve
	 * @return mixed The variable value
	 */
	public function PostRaw($name)
	{
		if (array_key_exists($name, $this->_post))
		{
			return $this->_post[$name];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Retrieves a value from the $_SERVER variables
	 * @param string $name The name of the variable key to retrieve
	 * @return mixed The variable value
	 */
	public function Server($name)
	{
		if (array_key_exists($name, $this->_server))
		{
			return $this->_server[$name];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Retrieves a value from the other variables
	 * @param string $name The name of the variable key to retrieve
	 * @return mixed The variable value
	 */
	public function Param($name)
	{
		return $this->Clean($this->ParamRaw($name));
		//return $this->ParamRaw($name);
	}

	/**
	 * Retrieves a value from the other variables
	 * @param string $name The name of the variable key to retrieve
	 * @return mixed The variable value
	 */
	public function ParamRaw($name)
	{
		if (!$this->_params_set)
		{
			throw new Krai_Request_Exception("You asked for a parameter before they were set.");
		}

		if (array_key_exists($name, $this->_params))
		{
			return $this->_params[$name];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Retrieves the request referer
	 * @return string The referer
	 */
	public function Referer()
	{
		return $this->Server("HTTP_REFERER");
	}


	/**
	 * Retrieves the uri information
	 * @return string The URI
	 */
	public function Uri()
	{
		return $this->_uri;
	}

	/**
	 * Retrieves the request method information
	 * @return string The Request Method
	 */
	public function RequestMethod()
	{
		return $this->Server("REQUEST_METHOD");
	}

}
