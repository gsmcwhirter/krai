<?php
/**
 * Krai inflector class
 * @package Krai
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Inflector class - changes case conventions and plurality
 * @package Krai
 * @subpackage Lib
 */
final class Krai_Lib_Inflector
{

  /**
   * Holds pluralizations
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
   * @var array
   */
  private $_uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

  /**
   * Holds irregular pluralizations
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
   * Constructor - doesn't do anything yet
   *
   */
  public function __construct()
  { }

  /**
   * Changes a string from CamelCase to underscore_case
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
