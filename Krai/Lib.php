<?php
/**
 * Krai functions library
 *
 * This file includes the required
 * bundled libraries, including {@link Krai_Lib_Inflector}, {@link Nakor},
 * and {@link Spyc}. The autoloader that was formerly included has been
 * deprecated in order to give more flexibility to application writers.
 *
 * @package Krai
 * @subpackage Lib
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Lib/Inflector.php",
//  Krai::$FRAMEWORK."/Lib/Nakor.php",
  Krai::$FRAMEWORK."/Lib/Spyc.php"
);
