<?php
/**
 * Nakor's Input Scrubberr
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
 * @package Krai
 * @subpackage Lib
 */
class Nakor
{

    /**
     * Determines whether magic quotes are on or not
     *
     * @var boolean
     */
    private $get_magic_quotes;

    /**
    * Clean Input
    *
    * Removes harmful tags from $HTTP_REQUEST_VARS
    *
    * @return    array
    */

    public function CleanInput($type)
    {
        $this->get_magic_quotes = get_magic_quotes_gpc();

        $return = array();


      if($type == "GET"){
        # ************************************************************************** #
        #
        # Clean $_GET data
        #
        # ************************************************************************** #


        /*if( is_array($_GET) )
        {
            while( list($k, $v) = each($_GET) )
            {
                if ( is_array($_GET[$k]) )
                {
                    while( list($k2, $v2) = each($_GET[$k]) )
                    {
                        $return[ $this->CleanKey($k) ][ $this->CleanKey($k2) ] = $this->CleanValue($v2);
                    }
                }
                else
                {
                    $return[ $this->CleanKey($k) ] = $this->CleanValue($v);
                }
            }
        }*/
        $return = $this->CleanArrayRec($_GET);


      }
      elseif($type == "POST"){

        # ************************************************************************** #
        #
        # Clean $_POST data
        #
        # ************************************************************************** #

        /*if( is_array($_POST) )
        {
            while( list($k, $v) = each($_POST) )
            {
                if ( is_array($_POST[$k]) )
                {
                    while( list($k2, $v2) = each($_POST[$k]) )
                    {
                        $return[ $this->CleanKey($k) ][ $this->CleanKey($k2) ] = $this->CleanValue($v2);
                    }
                }
                else
                {
                    $return[ $this->CleanKey($k) ] = $this->CleanValue($v);
                }
            }
        }*/
        $return = $this->CleanArrayRec($_POST);
      }

      //$return['request_method'] = strtolower($_SERVER['REQUEST_METHOD']);

      return $return;
    }

    /**
     * Recursively clean an array
     *
     * @param array $array
     * @return array
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
     * @param    string $key
     *
     * @return    string
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
     * @param    string $val
     *
     * @return    string
     */
    public function CleanValue($val)
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
        //$val = preg_replace( "/\n/"        , "<br />"        , $val );
        $val = preg_replace( "/\\\$/"      , "&#036;"        , $val );
        $val = preg_replace( "/\r/"        , ""              , $val );
        //$val = str_replace( "!"            , "&#33;"         , $val );
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
     * @param string $val
     * @return string
     */
    public function UncleanValue($val){
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
        $val = str_replace( "<br />"        , "\n"        , $val );
        $val = str_replace( "&#036;"      , "\$"        , $val );
        $val = str_replace( "&#33;"            , "!"         , $val );
        $val = str_replace( "&#39;"            , "'"         , $val );

        // Strip slashes if not already done so.
        if ( $this->get_magic_quotes )
        {
            $val = stripslashes($val);
        }

        return $val;
    }

}
