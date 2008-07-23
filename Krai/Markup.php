<?php
/**
 * Krai markup generator functions
 *
 * This file holds a class that provides some markup generation functionality.
 * It is currently not well developed.
 *
 * @package Krai
 * @subpackage Markup
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Markup/Exception.php"
);

/**
 * Markup generator class
 *
 * This class currently provides markup generators for <script> tags and <link>
 * tags for javascript and css file inclusion respectively.
 *
 * @package Krai
 * @subpackage Markup
 */
abstract class Krai_Markup
{

  /**
   * Generates javascript tags for some file names
   *
   * This function generates <script> tags for some collection of javascript
   * file names. The files are prefixed with the BASEURI configuration option
   * value and the javascripts directory.
   *
   * So, for example:
   * <code>
   * # This is the BASEURI line in the config file
   * BASEURI: mypath/test
   *
   * # Get the tags
   * Krai_Markup::JavascriptTag(array("file1.js","other/file2.js"));
   * # => the <script> tags for /mypath/test/javascripts/file1.js and
   *                            /mypath/test/javascripts/other/file2.js
   * </code>
   *
   * @param array $files An array of file names to generate a tag for
   * @return string The tags, concatenated together
   */
  public static function JavascriptTag(array $files)
  {
    $ret = "";
    foreach($files as $file)
    {
      $ret .= "<script src=\"".(Krai::GetConfig("BASEURI") == "" ? "" : "/".
                                Krai::GetConfig("BASEURI")).
                "/javascripts/".$file."\" type=\"text/javascript\"></script>\n";
    }

    return $ret;
  }

  /**
   * Generates link tags for some stylesheet files
   *
   * This function generates <link> tags for some collection of stylesheet file
   * names. The files are prefixed with the BASEURI configuration option value
   * and the css directory.
   *
   * So, for example:
   * <code>
   * # This is the BASEURI line in the config file
   * BASEURI: mypath/test
   *
   * # Get the tags
   * Krai_Markup::StylesheetTag(array("file1.css","other/file2.css"));
   * # => the <link> tags for /mypath/test/css/file1.css and
   *                          /mypath/test/css/other/file2.css
   * </code>
   *
   * @param array $files An array of file names for which to generate tags
   * @param string $media A list of the media to which the files should apply
   * @return string
   */
  public static function StylesheetTag(array $files, $media = "all")
  {
    $ret = "";
    foreach($files as $file)
    {
      $ret .= "<link href=\"".(Krai::GetConfig("BASEURI") == "" ? "" : "/".
                               Krai::GetConfig("BASEURI")).
                  "/css/".$file."\" rel=\"stylesheet\" type=\"text/css\"".
                  " media=\"".$media."\" />\n";
    }

    return $ret;
  }
}
