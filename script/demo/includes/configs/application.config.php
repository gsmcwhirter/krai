<?php
/**
 * The application configuration file.
 * @package Demo
 * @subpackage Config
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008 Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$INCLUDES."/lib/functions.lib.php"
);

/**
 * Contains the application settings
 * @package Demo
 * @subpackage Config
 */
abstract class SETTINGS
{
  /**
   * The name of the login cookie
   *
   *
   */
  const COOKIENAME = "MyLogin";

  /**
   * The domain for the login cookie
   *
   *
   */
  const COOKIE_DOMAIN = ".example.com";

  /**
   * A date formatting string to use by default.
   *
   */
  const DATE_STRING = "D, M. jS g:i A";

  /**
   * Holds the location of a mimetex.cgi to use
   * @var string
   */
  public static $MimetexUrl = "http://www.hallofkvasir.org/cgi-bin/mimetex.cgi";

  /**
   * Holds the rules for the Text_Wiki parser to use.
   * @var array
   *
   */
  public static $TextWikiRules = array(
        'Prefilter',
        'Delimiter',
        'Code',
        'Function',
        'Raw',
        'Anchor',
        'Heading',
        'Toc',
        'Horiz',
        'Nbsp',
        'Break',
        'Blockquote',
        'List',
        'Deflist',
        'Table',
        'Tex',
        'Image',
        'Phplookup',
        'Center',
        'Newline',
        'Paragraph',
        'Url',
        'Colortext',
        'Strong',
        'Emphasis',
        'Underline',
        'Tt',
        'Superscript',
        'Subscript',
        'Revise',
        'Tighten'
    );

}
