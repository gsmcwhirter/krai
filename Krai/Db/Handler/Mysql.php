<?php
/**
 * MySQL Database handler abstract class for the Krai
 * @package Krai
 * @subpackage Db
 * @author Greg McWhirter <gsmcwhirter@gmail.com>
 * @copyright Copyright (c) 2008, Greg McWhirter
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * MySQL database handler
 *
 * @package Krai
 * @subpackage Db
 */
class Krai_Db_Handler_Mysql extends Krai_Db_Handler
{
  /**
   * Holds the database connection proper
   *
   * @var mysqli
   */
  private $_dbc;

  /**
   * Constructor
   *
   * @param array $dbinfo Database connection information
   * @return void
   */
  public function __construct(array $dbinfo)
  {
    $this->_dbc = new mysqli($dbinfo['_host'], $dbinfo['_user'], $dbinfo['_pass'], $dbinfo['_name']);
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
    list($spats, $bpars) = $this->ParseQueryParams($params);
    $sql_real = preg_replace("#/\?#","?",preg_replace($spats, $bpars, $sql, 1));

    if($this->CONFIG["DEBUG"])
    {
      Krai_Base::Notice($sql);
      Krai_Base::Notice($sql_real);
    }

    $query = $this->_dbc->query($sql_real);

    $tstop = microtime(true);

    Krai::WriteLog($sql_real, Krai::LOG_DEBUG, array("sql"));

    if(!$query)
    {
      throw new Krai_Db_Exception($this->error("text"), $this->error("number"));
    }
    return ($query instanceOf mysqli_result) ? new Krai_Db_Query($query) : $query;

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

  /**
   * Fetch an object for a query
   *
   * @param Krai_Db_Query $qid The query
   * @return Krai_Db_Object The resulting object
   */
  public function Fetch(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed() && $this->Rows($qid) > 0) ? $qid->fetch_object("Krai_Db_Object") : null;
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row;
  }

  /**
   * Fetch an array for a query
   *
   * @param Krai_Db_Query $qid The query
   * @return array The resulting row
   */
  public function FetchArray(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed()) ? $qid->fetch_assoc() : null;
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row;
  }

  /**
   * Fetch just one row from the query
   *
   * @param Krai_Db_Query $qid The query
   * @return array
   */
  public function FetchOne(Krai_Db_Query &$qid)
  {
    $row = (!$qid->IsClosed()) ? $qid->fetch_row() : null;
    if(!$qid->IsClosed() && (!$row || $this->Rows($qid) == 1))
    {
      $qid->Close();
    }
    return $row[0];
  }

  /**
   * Get the number of rows from a query
   *
   * @param Krai_Db_Query $qid The query
   * @return integer
   */
  public function Rows(Krai_Db_Query $qid)
  {
    return $qid->num_rows;
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

  /**
   * Return the number of affected rows from the last query
   *
   * @return integer
   */
  public function Affected()
  {
    return $this->_dbc->affected_rows;
  }

  /**
   * Return the last insert id
   *
   * @return integer
   */
  public function Inserted()
  {
    return $this->_dbc->insert_id;
  }

  /**
   * Escape the value for sql
   *
   * @param mixed $val
   * @return mixed
   */
  public function Escape($val)
  {
    return $this->_dbc->escape_string(preg_replace("#\?#","/?", $val));
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

}
