<?php
/**
 * Krai markup generator functions
 * @package Krai
 * @subpackage Markup
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Markup/Exception.php"
);

/**
 * Markup generator class
 * @package Krai
 * @subpackage Markup
 */
abstract class Krai_Markup
{

  /**
   * Generates javascript tags for some file names
   *
   * @param array $files
   * @return string
   */
  public static function JavascriptTag(array $files)
  {
    $ret = "";
    foreach($files as $file)
    {
      $ret .= "<script src=\"".(Krai::GetConfig("BASEURI") == "" ? "" : "/".Krai::GetConfig("BASEURI"))."/javascripts/".$file."\" type=\"text/javascript\"></script>\n";
    }

    return $ret;
  }

  /**
   * Generates link tags for some stylesheet files
   * @param array $files
   * @return string
   */
  public static function StylesheetTag(array $files, $media = "all")
  {
    $ret = "";
    foreach($files as $file)
    {
      $ret .= "<link href=\"".(Krai::GetConfig("BASEURI") == "" ? "" : "/".Krai::GetConfig("BASEURI"))."/css/".$file."\" rel=\"stylesheet\" type=\"text/css\" media=\"".$media."\" />\n";
    }

    return $ret;
  }
}
