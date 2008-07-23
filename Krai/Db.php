<?php
/**
 * Database handler abstract class for the Krai Framework
 *
 * This file contains the database connection loading class used by the
 * framework.
 *
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Db/Handler.php",
  Krai::$FRAMEWORK."/Db/Query.php",
  Krai::$FRAMEWORK."/Db/Object.php",
  Krai::$FRAMEWORK."/Db/Exception.php",
  Krai::$FRAMEWORK."/Struct/Dbquery.php"
);

/**
 *
 * Database connection configuration and loading class
 *
 * This class provides functionality to determine the correct type of
 * {@link Krai_Db_Handler} to use for a certain database type. It also loads the
 * necessary files to instantiate a connection of that type.
 *
 * @package Krai
 * @subpackage Db
 */
class Krai_Db
{
  /**
   * Looks up the name and includes the file for the class handling a certain
   * type of database.
   *
   * This function is basically a switch on the types of databases supported by
   * the framework at any given time. Currently there is alpha support for a PDO
   * connection which should theoretically support many formats, and
   * beta-to-stable support for mysql through the {@link PHP_MANUAL#mysqli}
   * interface.
   *
   * @param string $_type The database type
   * @return string The name of the class
   * @throws Krai_Db_Exception
   */
  public static function ClassLookup($_type)
  {
    $dconf = Krai::GetConfig("CONFIG_DB");
    if($dconf["USE_PDO"])
    {
      Krai::Uses(
        Krai::$FRAMEWORK."/Db/Handler/Pdo.php"
      );
      return "Krai_Db_Handler_Pdo";
    }
    else
    {
      switch($_type)
      {
        case "mysql":
          Krai::Uses(
            Krai::$FRAMEWORK."/Db/Handler/Mysql.php"
          );
          return "Krai_Db_Handler_Mysql";
        default:
          throw new Krai_Db_Exception(
                        "Tried to load handler for unsupported database ".$_type
                        );
      }
    }
  }

}
