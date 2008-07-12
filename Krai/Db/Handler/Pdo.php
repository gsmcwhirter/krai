<?php
/**
 * PDO Database handler abstract class for the Krai
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Db/Querypdo.php"
);

/**
 * PDO database handler
 *
 * @package Krai
 * @subpackage Db
 */
class Krai_Db_Handler_Pdo extends Krai_Db_Handler
{
  /**
   * Holds the database connection proper
   *
   * @var PDO
   */
  private $_dbc;

  /**
   * Holds copies of prepared statements
   * @var array
   */
  private $_prepared_statements = array();

  /**
   * Holds the affected rows of the last query
   * @var integer
   */
  private $_last_query_affected = null;

  /**
   * Holds the error info of the last statement.
   */
  private $_last_statement_einfo;

  /**
   * Constructor
   *
   * @param array $dbinfo Database connection information
   * @return void
   */
  public function __construct(array $dbinfo)
  {
    $this->_dbc = new PDO(self::GetDSN($dbinfo), $dbinfo['_user'], $dbinfo['_pass']);
    parent::__construct();
  }

  /**
   * Process a query struct
   *
   * @param Krai_Struct_Dbquery $query The query struct
   * @return mixed
   */
  public function Process(Krai_Struct_Dbquery $query)
  {
    switch($query->action)
    {
      case "find":
        $sql = "SELECT ".((count($query->fields) > 0) ? implode(", ", $query->fields) : "*")." FROM ".$this->GetJoins($query->tables).(($query->conditions != "") ? " WHERE ".$query->conditions : "").(($query->order != "") ? " ORDER BY ". $query->order : "").(($query->limit != "") ? " LIMIT ".$query->limit : "");
        $q = $this->Query($sql, $query->parameters);

        if($query->limit != "1")
        {
          $ret = array();
          while($row = $this->Fetch($q))
          {
            $ret[] = $row;
          }
        }
        else
        {
          $ret = $this->Fetch($q);
        }
        return $ret;
        break;
      case "delete":
        $sql = "DELETE FROM ".$this->GetJoins($query->tables).(($query->conditions != "") ? " WHERE ".$query->conditions : "").(($query->limit != "") ? " LIMIT ".$query->limit : "");
        return $this->Query($sql, $query->parameters);
        break;
      case "insert":
        $ks = array();
        $vs = array();
        $vals = array();
        if(!$query->multiple)
        {
          $count = 1;
          foreach($query->fields as $k => $v)
          {
            $ks[] = $k;
            $vals[] = $v;
            $vs[] = "?";
          }

          $vss = "(".implode(", ", $vs).")";
        }
        else
        {
          $count = 0;
          foreach($query->fields as $k => $flds)
          {
            foreach(array_keys($flds[0]) as $fk)
            {
              $ks[] = $fk;
              $vs[] = "?";
            }

            foreach($flds as $fk => $fv)
            {
              $count += 1;
              $vals[] = $fv;
            }
          }

          $vss = array();
          $vsst = implode(", ",$vs);
          for($i = 0; $i < $count; $i++)
          {
            $vss[] = "(".$vsst.")";
          }
          $vss = implode(", ", $vss);
        }
        $ks = implode(", ", $ks);

        $sql = "INSERT INTO ".$this->GetJoins($query->tables)." (".$ks.") VALUES ".$vss;
        $q = $this->Query($sql, $vals);
        if($this->Affected() > 0)
        {
          return $this->Inserted();
        }
        else
        {
          return false;
        }
        break;
      case "update":
        $flds = array();
        $vals = array();
        foreach($query->fields as $k => $v)
        {
          if(in_array($k,$query->literals))
          {
            $flds[] = $k." = ".$v;
          }
          else
          {
            $flds[] = $k." = ?";
            $vals[] = $v;
          }
        }
        $flds = implode(", ", $flds);
        $sql = "UPDATE ".$this->GetJoins($query->tables)." SET ".$flds." WHERE ".$query->conditions.(($query->limit != "") ? " LIMIT ".$query->limit : "");
        $q = $this->Query($sql, array_merge($vals, $query->parameters));
        if($q)
        {
          return true;
        }
        else
        {
          return false;
        }
        break;
    }
  }

  /**
   * Execute a query
   *
   * @param string $sql The SQL
   * @param array $params Query parameters
   * @return mixed A Krai_Db_Query object or the result of the mysqli::query call
   * @throws Krai_Db_Exception
   */
  public function Query($sql, array $params = array())
  {
    $tstart = microtime(true);

    $stmtkey = md5($sql);
    if(!array_key_exists($stmtkey,$this->_prepared_statements))
    {
      $stmt = $this->_dbc->prepare($sql);
      $this->_prepared_statements[$stmtkey] = $stmt;
    }
    else
    {
      $stmt = $this->_prepared_statements[$stmtkey];
    }

    if($this->CONFIG["DEBUG"])
    {
      Krai_Base::Notice($sql." ".serialize($params));
    }

    $query = $stmt->execute($params);
    $this->_last_statement_einfo = $stmt->errorInfo();

    Krai::WriteLog($sql." ".serialize($params), Krai::LOG_DEBUG, array("sql"));

    if(!$query)
    {
      throw new Krai_Db_Exception($this->error("text"), $this->error("number"));
    }

    if(preg_match("#^SELECT\s#i",$sql))
    {
      if(preg_match("#^SELECT\s.*?COUNT.*?\sFROM\s#i", $sql))
      {
        $count = 1;
      }
      else
      {

        $sql2 = preg_replace("#^SELECT\s.*?\sFROM(\s)#i","SELECT COUNT(*) FROM$1",$sql);
        $stmtkey2 = md5($sql2);

        if(!array_key_exists($stmtkey2, $this->_prepared_statements))
        {
          $stmt2 = $this->_dbc->prepare($sql2);
          $this->_prepared_statements[$stmtkey2] = $stmt2;
        }
        else
        {
          $stmt2 = $this->_prepared_statements[$stmtkey2];
        }

        Krai::WriteLog($sql2." ".serialize($params), Krai::LOG_DEBUG, array("sql"));

        $countq = $stmt2->execute($params);
        $count = $stmt2->fetchColumn();
      }
      $sel = true;
    }
    else
    {
      $count = $stmt->rowCount();
      $sel = false;
      $this->_last_query_affected = $count;
    }

    $tstop = microtime(true);


    $ret = ($query && $sel) ? new Krai_Db_Querypdo($stmt, $count) : (($query) ? $count : $query);
    Krai::WriteLog(serialize(gettype($ret))." ".serialize($count), Krai::LOG_DEBUG, array("sql"));
    return $ret;

  }

  /**
   * Works on aspects of a transaction (starting, committing, rolling back)
   * @param string $_action One of "start", "commit", or "rollback"
   * @throws Krai_Db_Exception
   *
   */
  public function Transaction($_action)
  {
    switch(strtolower($_action))
    {
      case "start":
        if(!$this->_dbc->beginTransaction())
        {
          throw new Krai_Db_Exception("Unable to start transaction.");
        }
        break;
      case "commit":
        if(!$this->_dbc->commit())
        {
          throw new Krai_Db_Exception("Unable to commit transaction.");
        }
        break;
      case "rollback":
        if(!$this->_dbc->rollBack())
        {
          throw new Krai_Db_Exception("Unable to rollback transaction.");
        }
        break;
      default:
        throw new Krai_Db_Exception("Un-recognized transaction command.");
    }
  }

  /**
   * Parse the query parameters, escaping and whatnot
   *
   * @param array $params The raw parameters
   * @throws Krai_Db_Exception
   * @return array Regexes for replacement and the clean parameters
   */
  protected function ParseQueryParams(array $params = array())
  {
    throw new Krai_Db_Exception("ParseQueryParams not implemented.");
  }

  /**
   * Fetch an object for a query
   *
   * @param Krai_Db_Query $qid The query
   * @return Krai_Db_Object The resulting object
   */
  public function Fetch(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed() && $this->Rows($qid) > 0) ? $qid->fetchObject("Krai_Db_Object") : null;
    Krai::WriteLog(serialize($qid->IsClosed())." ".serialize($this->Rows($qid))." ".serialize($row), Krai::LOG_DEBUG, array("sql"));
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row;
  }

  /**
   * Fetch an array for a query
   *
   * @param Krai_DbQuery $qid The query
   * @return array The resulting row
   */
  public function FetchArray(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed()) ? $qid->fetch(PDO::FETCH_ASSOC) : null;
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row;
  }

  /**
   * Fetch a non-associative array for the query
   *
   * @param Krai_Db_Query $qid The query
   * @return array
   */
  public function FetchOne(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed()) ? $qid->fetch(PDO::FETCH_NUM) : null;
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row[0];
  }

  /**
   * Get the number of rows from a query
   *
   * @param Krai_DbQuery $qid The query
   * @return integer
   */
  public function Rows(Krai_Db_Query $qid)
  {
    return $qid->NumRows();
  }

  /**
   * Get the error from a query
   *
   * @param string $ret One of "text", "number", or "array" for the desired format
   * @return mixed
   * @throws Krai_Db_Exception
   */
  public function Error($ret)
  {
    $einfo = $this->_last_statement_einfo;
    $einfo = (count($einfo) > 0) ? $einfo : $this->_dbc->errorInfo();

    if($ret == "text")
    {
      return $einfo[2];
    }
    elseif($ret == "number")
    {
      return $einfo[0];
    }
    elseif($ret == "array")
    {
      return array($einfo[2], $einfo[1]);
    }
    else
    {
      throw new Krai_Db_Exception("Un-recognized return type option passed to Krai_Db_Handler_Pdo::Error.");
    }
  }

  /**
   * Return the number of affected rows from the last query
   *
   * @return integer
   */
  public function Affected()
  {
    return $this->_last_query_affected;
  }

  /**
   * Return the last insert id
   *
   * @return integer
   */
  public function Inserted()
  {
    return $this->_dbc->lastInsertId();
  }

  /**
   * Escape the value for sql
   *
   * @param mixed $val
   * @return mixed
   * @throws Krai_Db_Exception
   */
  public function Escape($val)
  {
    throw new Krai_Db_Exception("Escape is not implemented.");
  }

  /**
   * Generate the joins from a table array
   *
   * @param array $tables Array of tables to join
   * @return string The join syntax
   */
  protected function GetJoins(array $tables)
  {
    if(count($tables) == 1)
    {
      return $tables[0];
    }
    else
    {
      $main = array_shift($tables);
      $ljoins = array();
      $ijoins = array();
      foreach($tables as $k => $v)
      {
        if(is_string($k))
        {
          $ljoins[] = "LEFT JOIN ".$k." ON ".$v;
        }
        else
        {
          $ijoins[] = $v;
        }
      }

      array_unshift($ijoins, $main);

      $ljoins = implode(" ", $ljoins);
      $ijoins = implode(", ", $ijoins);

      return $ijoins." ".$ljoins;
    }
  }

  /**
   * Generates DSN strings for a variety of databases from the $dbinfo
   * @param array $dbinfo Database connection information
   * @return string
   * @throws Krai_DbException
   */
  public static function GetDSN(array $dbinfo)
  {
    switch($dbinfo['_type'])
    {
      case 'mysql':
        if(array_key_exists("_socket", $dbinfo))
        {
          return 'mysql:unix_socket='.$dbinfo['_socket'].';dbname='.$dbinfo['_name'];
        }
        else
        {
          return 'mysql:host='.$dbinfo['_host'].';'.(array_key_exists("_port", $dbinfo) ? "port=".$dbinfo['_port'].";" : "").'dbname='.$dbinfo['_name'];
        }
      case 'pgsql':
        return 'pgsql:host='.$dbinfo['_host'].';'.(array_key_exists("_port", $dbinfo) ? "port=".$dbinfo['_port'].';' : "").'dbname='.$dbinfo['_name'];
      case 'sqlite2':
        return 'sqlite2:'.$dbinfo['_name'];
      case 'sqlite':
      case 'sqlite3':
        return 'sqlite:'.$dbinfo['_name'];
      case 'oracle':
        return 'oci:'.$dbinfo['_name'];
      default:
        throw new Krai_Db_Exception("Un-supported database type requested.");
    }
  }

}
