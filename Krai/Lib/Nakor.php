<?php
/**
 * Nakor's Input Scrubber
 *
 * This file contains an input scrubber
 *
 * @package Krai
 * @subpackage Lib
 * @author Nakor <nakor@clantemplates.com>
 * @copyright Copyright &copy; 2005, Nakor
 */
# ************************************************************************** #
# Core class
# ************************************************************************** #
# Version:        1.0
# Date Started:   May 1st, 2005
# Date Finished:  May 2nd, 2005
# Author:         Nakor
# Email:          Nakor@clantemplates.com
# ************************************************************************** #
# Miscellaneous core methods.
# ************************************************************************** #


# ************************************************************************** #
# Start the core class
# ************************************************************************** #

/**
 * Nakor's Input scrubber
 *
 * This class provides some servicable input scrubbing.
 *
 * @package Krai
 * @subpackage Lib
 */
class Nakor
{

    /**
     * Determines whether magic quotes are on or not
     *
     * This variable holds the value of the determination of whether magic_quotes_gpc
     * is enabled for the server.
     *
     * @var boolean
     */
    private $get_magic_quotes;

    /**
    * Clean Input
    *
    * This function sanitizes input in either the $_GET or $_POST arrays based on
    * the $type parameter. It basically just passes off to {@link Nakor::CleanArrayRec()}.
    *
    * @param string $type One of "GET" or "POST" representing which array to clean.
    * @return array A sanitized copy of the array
    */
    public function CleanInputAuto($type)
    {
        $this->get_magic_quotes = get_magic_quotes_gpc();
        $return = array();

      if($type == "GET"){
        # ************************************************************************** #
        #
        # Clean $_GET data
        #
        # ************************************************************************** #

        $return = $this->CleanArrayRec($_GET);


      }
      elseif($type == "POST"){

        # ************************************************************************** #
        #
        # Clean $_POST data
        #
        # ************************************************************************** #

        $return = $this->CleanArrayRec($_POST);
      }

      return $return;
    }

	public function CleanInput($input)
	{
		$this->get_magic_quotes = get_magic_quotes_gpc();

		if(is_array($input))
		{
			return $this->CleanArrayRec($input);
		}
		else
		{
			return $this->CleanValue($input);
		}
	}

    /**
     * Recursively clean an array
     *
     * This function recursively cleans the keys and values of an array.
     *
     * @param array $array The array to clean
     * @return array The cleaned array
     */
    private function CleanArrayRec(array $array)
    {
      $ret = array();
      foreach($array as $k => $v)
      {
        $ret[$this->CleanKey($k)] = (is_array($v)) ? $this->CleanArrayRec($v) : $this->CleanValue($v);
      }

      return $ret;
    }

    /**
     * Clean Key
     *
     * Removes harmful tags from a variable key
     *
     * @param    string $key The key to clean
     * @return    string The cleaned key
     */
    private function CleanKey($key)
    {
        if ($key == "")
        {
            return "";
        }

        $key = htmlspecialchars(urldecode($key));
        $key = preg_replace( "/\.\./"           , ""  , $key );
        $key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );
        $key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );

        return $key;
    }



    /**
     * Clean Value
     *
     * Removes harmful tags from a variable value
     *
     * @param    string $val The value to clean
     * @return    string The cleaned value
     */
    private function CleanValue($val)
    {
        if ($val == "")
        {
            return null;
        }

        $val = str_replace( "&#032;", " ", $val );
        $val = str_replace( "&"            , "&amp;"         , $val );
        $val = str_replace( "<!--"         , "&#60;&#33;--"  , $val );
        $val = str_replace( "-->"          , "--&#62;"       , $val );
        $val = preg_replace( "/<script/i"  , "&#60;script"   , $val );
        $val = str_replace( ">"            , "&gt;"          , $val );
        $val = str_replace( "<"            , "&lt;"          , $val );
        $val = str_replace( "\""           , "&quot;"        , $val );
        $val = preg_replace( "/\\\$/"      , "&#036;"        , $val );
        $val = preg_replace( "/\r/"        , ""              , $val );
        $val = str_replace( "'"            , "&#39;"         , $val );
        $val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val );

        // Strip slashes if not already done so.
        if ( $this->get_magic_quotes )
        {
            $val = stripslashes($val);
        }

        // Swap user inputted backslashes
        $val = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $val );

        return $val;
    }

    /**
     * Reverse variable cleaning
     *
     * This function can un-scrub a variable value
     *
     * @param string $val The value to un-sanitize
     * @return string The unsanitized value
     */
    private function UncleanValue($val){
      if ($val == "")
        {
            return "";
        }

        $val = str_replace( "&amp;"            , "&"         , $val );
        $val = str_replace( "&#60;&#33;--"     , "<!--"      , $val );
        $val = str_replace( "--&#62;"          , "-->"       , $val );
        $val = str_replace( "&#60;script"     , "<script", $val );
        $val = str_replace( "&gt;"             , ">"         , $val );
        $val = str_replace( "&lt;"            , "<"          , $val );
        $val = str_replace( "&quot;"           , "\""        , $val );
        $val = str_replace( "&#036;"      , "\$"        , $val );
        $val = str_replace( "&#39;"            , "'"         , $val );

        // Strip slashes if not already done so.
        if ( $this->get_magic_quotes )
        {
            $val = stripslashes($val);
        }

        return $val;
    }

}
