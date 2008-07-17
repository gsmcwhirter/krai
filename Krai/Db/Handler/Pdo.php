<?php
/**
 * PDO Database handler abstract class for the Krai Framework
 *
 * This file includes a database handler using the {@link PHP_MANUAL#PDO} library.
 *
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

Krai::Uses(
  Krai::$FRAMEWORK."/Db/Querypdo.php"
);

/**
 * PDO database handler
 *
 * This is a database handler implementing a wrapper around the {@link PHP_MANUAL#PDO}
 * functionality. THIS IS STILL ALPHA QUALITY. USE AT YOUR OWN RISK.
 *
 * @package Krai
 * @subpackage Db
 */
class Krai_Db_Handler_Pdo extends Krai_Db_Handler
{
  /**
   * Holds the database connection proper
   *
   * This variable holds the actual connection to the database.
   *
   * @var PDO
   */
  private $_dbc;

  /**
   * Holds copies of prepared statements
   *
   * @var array
   */
  private $_prepared_statements = array();

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
        $res = $this->Query($sql, $query->parameters);
        if($res instanceOf Krai_Db_Query)
        {
          return $this->Affected($res);
        }
        else
        {
          return $res;
        }
        //return $this->Query($sql, $query->parameters);
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
        if($this->Affected($q) > 0)
        {
          return $this->Inserted($q);
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
        if($q instanceOf Krai_Db_Query)
        {
          return $this->Affected($q);
        }
        else
        {
          return $q;
        }
        break;
    }
  }

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

    Krai::WriteLog($sql." ".serialize($params), Krai::LOG_DEBUG, array("sql"));

    if(!$query)
    {
      throw new Krai_Db_Exception($this->Error("text"), is_integer($this->Error("number")) ? $this->Error("number") : 0);
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
      $sel = false;
    }

    $tstop = microtime(true);


    $ret = ($query) ? new Krai_Db_Querypdo($stmt, ($sel) ? $count : $query) : $query;
    Krai::WriteLog(serialize(gettype($ret))." ".serialize($count), Krai::LOG_DEBUG, array("sql"));
    return $ret;

  }

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

  public function FetchArray(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed()) ? $qid->fetch(PDO::FETCH_ASSOC) : null;
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row;
  }

  public function FetchOne(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed()) ? $qid->fetch(PDO::FETCH_NUM) : null;
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row[0];
  }

  public function Rows(Krai_Db_Query $qid)
  {
    return $qid->NumRows();
  }

  public function Error(Krai_Db_Query $qid, $ret)
  {
    $stmt = $qid->GetQuery();
    $einfo = $stmt->errorInfo();
    $einfo = (count($einfo) > 0) ? $einfo : $this->_dbc->errorInfo();

    if($ret == "text")
    {
      return array_key_exists(2, $einfo) ? $einfo[2] : "Unknown error.";
    }
    elseif($ret == "number")
    {
      return array_key_exists(0, $einfo) ? $einfo[0] : "Unknown error.";
    }
    elseif($ret == "array")
    {
      return array(array_key_exists(2, $einfo) ? $einfo[2] : "Unknown error.", array_key_exists(1, $einfo) ? $einfo[1] : "Unknown error.");
    }
    else
    {
      throw new Krai_Db_Exception("Un-recognized return type option passed to Krai_Db_Handler_Pdo::Error.");
    }
  }

  public function Affected(Krai_Db_Query $qid)
  {
    $stmt = $qid->GetQuery();
    return $stmt->rowCount();
  }

  public function Inserted(Krai_Db_Query $qid)
  {
    return $this->_dbc->lastInsertId();
  }

  /**
   * Generates DSN strings for a variety of databases from the $dbinfo
   *
   * This function processes database info into the DSN strings that PDO needs in
   * order to connect to the database.
   *
   * @param array $dbinfo Database connection information
   * @return string
   * @throws Krai_Db_Exception
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
