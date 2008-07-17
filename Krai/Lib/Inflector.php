<?php
/**
 * Krai inflector class
 *
 * This file holds the inflector class used by the framework.
 *
 * @package Krai
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Inflector class - changes case conventions and plurality
 *
 * This class provides functionality to convert words from singluar to plural and
 * vice versa. Additionally, it can change things from CamelCase to lowercase_with_underscores.
 *
 * @package Krai
 * @subpackage Lib
 */
final class Krai_Lib_Inflector
{

  /**
   * Holds pluralizations
   *
   * This is an array of pluralization regular expression patterns and replacements
   *
   * @var array
   */
  private $_plural = array(
    '/(quiz)$/i' => '$1zes',
    '/^(ox)$/i' => '$1en',
    '/([m|l])ouse$/i' => '$1ice',
    '/(matr|vert|ind)ix|ex$/i' => '$1ices',
    '/(x|ch|ss|sh)$/i' => '$1es',
    '/([^aeiouy]|qu)ies$/i' => '$1y',
    '/([^aeiouy]|qu)y$/i' => '$1ies',
    '/(hive)$/i' => '$1s',
    '/(?:([^f])fe|([lr])f)$/i' => '$1$2ves',
    '/sis$/i' => 'ses',
    '/([ti])um$/i' => '$1a',
    '/(buffal|tomat)o$/i' => '$1oes',
    '/(bu)s$/i' => '$1ses',
    '/(alias|status)/i'=> '$1es',
    '/(octop|vir)us$/i'=> '$1i',
    '/(ax|test)is$/i'=> '$1es',
    '/s$/i'=> 's',
    '/$/'=> 's'
	);

  /**
   * Holds singular-izations
   *
   * This is an array of singularization regular expression patterns and replacements
   *
   * @var array
   */
  private $_singular = array (
    '/(quiz)zes$/i' => '\\1',
    '/(matr)ices$/i' => '\\1ix',
    '/(vert|ind)ices$/i' => '\\1ex',
    '/^(ox)en/i' => '\\1',
    '/(alias|status)es$/i' => '\\1',
    '/([octop|vir])i$/i' => '\\1us',
    '/(cris|ax|test)es$/i' => '\\1is',
    '/(shoe)s$/i' => '\\1',
    '/(o)es$/i' => '\\1',
    '/(bus)es$/i' => '\\1',
    '/([m|l])ice$/i' => '\\1ouse',
    '/(x|ch|ss|sh)es$/i' => '\\1',
    '/(m)ovies$/i' => '\\1ovie',
    '/(s)eries$/i' => '\\1eries',
    '/([^aeiouy]|qu)ies$/i' => '\\1y',
    '/([lr])ves$/i' => '\\1f',
    '/(tive)s$/i' => '\\1',
    '/(hive)s$/i' => '\\1',
    '/([^f])ves$/i' => '\\1fe',
    '/(^analy)ses$/i' => '\\1sis',
    '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
    '/([ti])a$/i' => '\\1um',
    '/(n)ews$/i' => '\\1ews',
    '/s$/i' => '',
  );

  /**
   * List of nouns which are not pluralizable
   *
   * This is an array of some nouns which do not change from singular to plural
   *
   * @var array
   */
  private $_uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

  /**
   * Holds irregular pluralizations
   *
   * This is an array of irregular pluralizations
   *
   * @var array
   */
  private $_irregular = array(
    'person' => 'people',
    'man' => 'men',
    'child' => 'children',
    'sex' => 'sexes',
    'move' => 'moves'
  );

  /**
   * Constructor - doesn't do anything
   *
   * This doesn't do anything except create a new instance.
   *
   */
  public function __construct()
  { }

  /**
   * Changes a string from CamelCase to underscore_case
   *
   * This function takes a string and replaces capital letters by lowercase letters
   * preceded by underscores (except the first letter of the new string will never)
   * be an underscore unless the first letter of the original was).
   *
   * @param string $str
   * @return string
   */
  public function Camel2Underscore($str)
  {
    $new = preg_replace("/([^A-Z_]+)([A-Z]{1})/","$1_$2",$str);
    return strtolower(((substr($new, 0, 1) == "_" && substr($str,0,1) != "_") ? substr($new, 1) : $new));
  }

  /**
   * Changes a string from underscore_case to CamelCase
   *
   * This function takes a string and capitalizes the first letter and any letter
   * preceded by an underscore character. It also capitalizes the first letter of
   * the string.
   *
   * @param string $str
   * @return string
   */
  public function Underscore2Camel($str)
  {
    $new = preg_replace("/_([a-zA-Z]{1})/"," $1", $str);
    return preg_replace("/ /","",ucwords($new));
  }

  /**
   * Changes a noun from singular to plural
   *
   * This function takes a word and attempts to make it plural. It returns false
   * if it cannot figure out how.
   *
   * @param string $word
   * @return string
   */
  public function Pluralize($word)
  {
    $lowercased_word = strtolower($word);

    foreach ($this->_uncountable as $_uncountable)
    {
      if(substr($lowercased_word,(-1*strlen($_uncountable))) == $_uncountable)
      {
        return $word;
      }
    }

    foreach ($this->_irregular as $_plural => $_singular)
    {
      if (preg_match('/('.$_plural.')$/i', $word, $arr))
      {
        return preg_replace('/('.$_plural.')$/i', substr($arr[0],0,1).substr($_singular,1), $word);
      }
    }

    foreach ($this->_plural as $rule => $replacement)
    {
      if (preg_match($rule, $word)) {
        return preg_replace($rule, $replacement, $word);
      }
    }
    return false;
  }

  /**
   * Changes a word from plural to singular
   *
   * This function takes a word and attempts to singularize it. It returns false
   * if it cannot figure out how.
   *
   * @param string $word
   * @return string
   */
  public function Singular($word)
  {
    $lowercased_word = strtolower($word);
    foreach ($this->_uncountable as $_uncountable)
    {
      if(substr($lowercased_word,(-1*strlen($_uncountable))) == $_uncountable)
      {
        return $word;
      }
    }

    foreach ($this->_irregular as $_plural=> $_singular)
    {
      if (preg_match('/('.$_singular.')$/i', $word, $arr))
      {
        return preg_replace('/('.$_singular.')$/i', substr($arr[0],0,1).substr($_plural,1), $word);
      }
    }

    foreach ($this->_singular as $rule => $replacement)
    {
      if (preg_match($rule, $word))
      {
        return preg_replace($rule, $replacement, $word);
      }
    }

    return $word;
  }
}
