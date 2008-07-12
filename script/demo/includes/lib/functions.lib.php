<?php
/**
 * The application configuration file.
 * @package Demo
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008
 */

/**
 * Walks over an array applying a function to the values (by reference)
 * @param mixed $value The value
 * @param mixed $key The key of the value
 * @param callback $function The name of the function to apply
 */
function my_array_walk(&$value, $key, $function)
{
  $value = call_user_func($function, $value);
}

/**
 * Transforms a hexadecimal string into binary
 * @param string $data
 * @return string
 */
function hex2bin($data) {
  $newdata = "";
  $len = strlen($data);
  for($i=0;$i<$len;$i+=2) {
    $newdata .= pack("C",hexdec(substr($data,$i,2)));
  }
  return $newdata;
}

/**
 * Fills zeros on the left of a value to a specified length
 * @param mixed $str The value to prepend the zeros to
 * @param integer $len The minimum length of the resulting string
 * @return string
 *
 */
function ZeroFill($str, $len)
{
  for($i = strlen($str); $i < $len; $i++)
  {
    $str = "0".$str;
  }
  return $str;
}

/**
 * Some light e-mail address obfuscation. Changes "username@domain.com" to "username -at- domain -dot- com"
 * @param string $email
 * @return string
 */
function HideEmail($email)
{
  return preg_replace(array("#@#","#\.#"), array(" -at- "," -dot- "), $email);
}

/**
 * Un-does some input scrubbing
 * @param string $val The value to unscrub
 * @return string
 */
function UnscrubValue($val)
{
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
        if ( get_magic_quotes_gpc() )
        {
            $val = stripslashes($val);
        }

        return $val;
}
