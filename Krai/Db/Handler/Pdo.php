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
      case "select":
        $sql = "SELECT ".((count($query->fields) > 0) ? implode(", ", $query->fields) : "*")." FROM ".$this->GetJoins($query->tables).(($query->conditions != "") ? " WHERE ".$query->conditions : "").(($query->order != "") ? " ORDER BY ". $query->order : "").(($query->limit != "") ? " LIMIT ".$query->limit : "");
        $q = $this->Query("select", $sql, $query->parameters);

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
        return $this->Query("delete", $sql, $query->parameters);
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
        return $this->Query("insert", $sql, $vals);
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
        return $this->Query("update",$sql, array_merge($vals, $query->parameters));
        break;
    }
  }

  public function Query($querytype, $sql, array $params = array())
  {
    if(!in_array($querytype, array("select","update","insert","delete","transaction")))
    {
      throw new Krai_Db_Exception("Unrecognized query type provided.");
    }
    $tstart = microtime(true);

    $stmtkey = md5($sql);
    if(!array_key_exists($stmtkey, $this->_prepared_statements))
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
      print $sql." ".serialize($params);
    }

    $query = $stmt->execute($params);

    if($querytype == "select")
    {
      if(preg_match("#^SELECT\s.*?COUNT.*?\sFROM\s#i", $sql))
      {
        $count = 1;
      }
      else
      {
        $sql2 = preg_replace("#^SELECT\s.*?\sFROM(\s)#i","SELECT COUNT(*) FROM$1",$sql,1);
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

        $countq = $stmt2->execute($params);
        $count = $stmt2->fetchColumn();
        Krai::WriteLog($sql2." ".serialize($params), Krai::LOG_DEBUG, array("sql"));
      }
    }

    $tstop = microtime(true);

    Krai::WriteLog($sql." ".serialize($params)." :: ".($tstop - $tstart)."s", Krai::LOG_DEBUG, array("sql"));

    $error = $stmt->errorInfo();
    if(is_array($error) && count($error) == 3)
    {
      $error = array($error[2], $error[1]);
    }
    else
    {
      $error = array(null, null);
    }

    return new Krai_Db_Query($stmt, array(
      "affected" => ($querytype != "select" && $querytype != "transaction") ? $stmt->rowCount() : null,
      "insertid" => ($querytype == "insert") ? $this->_dbc->lastInsertId() : null,
      "numrows" => ($querytype == "select") ? $count : null,
      "successful" => $query,
      "error" => $error
    ));

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
    $row = (!$qid->IsClosed() && $qid->NumRows() > 0) ? $qid->fetchObject("Krai_Db_Object") : null;
    if(!$qid->IsClosed() && (!$row || $qid->NumRows() == 1))
    {
      $qid->Close();
    }
    return $row;
  }

  public function FetchArray(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed() && $qid->NumRows() > 0) ? $qid->fetch(PDO::FETCH_ASSOC) : null;
    if(!$qid->IsClosed() && (!$row || $qid->NumRows() == 1))
    {
      $qid->Close();
    }
    return $row;
  }

  public function FetchOne(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed() && $qid->NumRows() > 0) ? $qid->fetch(PDO::FETCH_NUM) : null;
    if(!$qid->IsClosed() && (!$row || $qid->NumRows() == 1))
    {
      $qid->Close();
    }
    return $row[0];
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
