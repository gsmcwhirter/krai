<?php
/**
 * MySQL Database handler abstract class for the Krai Framework
 *
 * This file holds the database handler for mysql databases used by the framework.
 *
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * MySQL database handler
 *
 * This class is a wrapper around the {@link PHP_MANUAL#mysqli} database connection
 * functionality, implementing the Krai_Db_Handler pattern.
 *
 * @package Krai
 * @subpackage Db
 */
class Krai_Db_Handler_Mysql extends Krai_Db_Handler
{
  /**
   * Holds the database connection proper
   *
   * This variable holds the actual database connection using {@link PHP_MANUAL#mysqli}
   *
   * @var mysqli
   */
  private $_dbc;

  /**
   * Constructor
   *
   * This function initializes the database with the provided info. Expected array
   * keys are '_host', '_user', '_pass', and '_name'.
   *
   * @param array $dbinfo Database connection information
   * @return void
   */
  public function __construct(array $dbinfo)
  {
    $this->_dbc = new mysqli($dbinfo['_host'], $dbinfo['_user'], $dbinfo['_pass'], $dbinfo['_name']);
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
    list($spats, $bpars) = $this->ParseQueryParams($params);
    $sql_real = preg_replace("#/\?#","?",preg_replace($spats, $bpars, $sql, 1));

    if($this->CONFIG["DEBUG"])
    {
      Krai::Notice($sql);
      Krai::Notice($sql_real);
    }

    $query = $this->_dbc->query($sql_real);

    $tstop = microtime(true);

    Krai::WriteLog($sql_real, Krai::LOG_DEBUG, array("sql"));

    if(!$query)
    {
      throw new Krai_Db_Exception($this->error("text"), $this->error("number"));
    }
    return new Krai_Db_Query(($query instanceOf mysqli_result) ? $query : array($query, $this->_dbc->affected_rows, $this->_dbc->insert_id));

  }

  public function Transaction($_action)
  {
    switch(strtolower($_action))
    {
      case "start":
        if(!$this->Query("START TRANSACTION"))
        {
          throw new Krai_Db_Exception("Unable to start transaction.");
        }
        break;
      case "commit":
        if(!$this->Query("COMMIT"))
        {
          throw new Krai_Db_Exception("Unable to commit transaction.");
        }
        break;
      case "rollback":
        if(!$this->Query("ROLLBACK"))
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
   * This function is a replacement for prepared statements with mysqli since
   * the parameter passing to that and data retrieval is terribly un-elegant.
   *
   * This function generates regular expressions to pick out the '?' terms in the
   * sql query and an array of replacement syntax, having used {@link Krai_Db_Handler_Mysql::Escape()}
   * to clean the values.
   *
   * @param array $params The raw parameters
   * @return array Regexes for replacement and the clean parameters
   */
  protected function ParseQueryParams(array $params = array())
  {
    if(count($params) == 0)
    {
      return array(array(),array());
    }
    $vals = array();
    $pats = array();
    foreach($params as $p)
    {
      switch ( gettype( $p ) )
      {
        case 'integer':
          $vals[] = "$1 ".$this->Escape($p);
          break;
        case 'double':
          $vals[] = "$1 ".$this->Escape($p);
          break;
        case 'string':
          $vals[] = "$1 '".$this->Escape($p)."'";
          break;
        case 'boolean':
          $vals[] = "$1 ".(($p) ? "'true'" : "'false'");
          break;
        case 'array':
          $vals[] = "$1 '".$this->Escape(implode(",", $p))."'";
          break;
        case 'object':
        case 'resource':
        case 'NULL':
          $vals[] = "$1 NULL";
        default:
          break;
      }
    }
    for($i = 0; $i < count($vals); $i++)
    {
      $pats[] = "#([=,\s(]{1})\?#";
    }
    return array($pats, $vals);

  }

  public function Fetch(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed() && $this->Rows($qid) > 0) ? $qid->fetch_object("Krai_Db_Object") : null;
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row;
  }

  public function FetchArray(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed()) ? $qid->fetch_assoc() : null;
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row;
  }

  public function FetchOne(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed()) ? $qid->fetch_row() : null;
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row[0];
  }

  public function Rows(Krai_Db_Query $qid)
  {
    return $qid->num_rows;
  }

  public function Error(Krai_Db_Query $qid, $ret)
  {
    if($ret == "text")
    {
      return $this->_dbc->error;
    }
    elseif($ret == "number")
    {
      return $this->_dbc->errno;
    }
    elseif($ret == "array")
    {
      return array($this->_dbc->error, $this->_dbc->errno);
    }
    else
    {
      throw new Krai_Db_Exception("Un-recognized return type option passed to Krai_DbMysql::Error.");
    }
  }

  public function Affected(Krai_Db_Query $qid)
  {
    $d = $qid->GetQuery();
    if(is_array($d))
    {
      return $d[1];
    }
  }

  public function Inserted(Krai_Db_Query $qid)
  {
    $d = $qid->GetQuery();
    if(is_array($d))
    {
      return $d[2];
    }
  }

  public function Result(Krai_Db_Query $qid)
  {
    $d = $qid->GetQuery();
    if(is_array($d))
    {
      return $d[0];
    }
  }

  /**
   * Escape the parameter so it is safe to insert into a query
   *
   * This function escapes a value so it is safe to use in an sql query
   *
   * @param mixed $val
   * @return mixed
   */
  protected function Escape($val)
  {
    return $this->_dbc->escape_string(preg_replace("#\?#","/?", $val));
  }

}
