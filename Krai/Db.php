<?php
/**
 * Database handler abstract class for the Krai
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
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
 * @package Krai
 * @subpackage Db
 */
class Krai_Db
{
  /**
   * Looks up the name and includes the file for the class handling a certain type
   * of database.
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
          throw new Krai_Db_Exception("Tried to load handler for unsupported database ".$_type);
      }
    }
  }

}
