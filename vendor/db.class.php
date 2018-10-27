<?php
//$this->db->join('comments', 'comments.id = blogs.id', 'left');
class db
{

    public $connection;
    public $host;
    public $user;
    public $pw;
    public $name;
    public $prefix;
    public $lastResult;
    public $affectedRows;
    public $lastId;
    public $dostripslashes = true;
    public $qCount = 0;
    public $_dbRead;
    private $db_type;
    protected $_join = array();

    function __construct ($dbs, $action)
    {
        if ($action == '_read') {
            self::_dbRead ($dbs);
        } else if ($action == '_write') {
            self::_dbWrite ($dbs);
        } else {
            die ("ERROR(DB): bad action");
        }
    }
    
    function _dbRead($dbs)
    {
    $a = $dbs;
    shuffle ($a);

    foreach ($a as $v) {
        $this->host = $v['host'];
        $this->user = str_repeat("*", strlen($v['user']));
        $this->pw = str_repeat("*", strlen($v['password']));
        $this->name = $v['db'];
        $this->prefix = $v['prefix'];

        $xdebug = ini_get ('xdebug.halt_level');
        if ($xdebug !== FALSE) {
            ini_set ('xdebug.halt_level', 0);
        }



        if (isset ($v['port'])) {
            $this->connection = @mysqli_connect ($v['host'], $v['user'], $v['password'], $v['db'], $v['port']);
        } else {
            $this->connection = @mysqli_connect ($v['host'], $v['user'], $v['password'], $v['db']);
            //mysqli_set_charset ($this->connection, "utf8");
            
            
        }

        if ($xdebug !== FALSE) {
            ini_set ('xdebug.halt_level', $xdebug);
        }

        if ($this->connection) {
            //$this->connection->set_charset("utf8");

            @mysqli_set_charset ($this->connection, "utf8");
           
            return;
        }



        error_log ("ERROR: Failed to connect to DB server: " . $v['host']);
    }

    die(json_encode(array('error' => 'Unable to connect to DB')));
    }
    
    function _dbWrite($dbs)
    {
    foreach ($dbs as $v) {
        $this->host = $v['host'];
        $this->user = str_repeat("*", strlen($v['user']));
        $this->pw = str_repeat("*", strlen($v['password']));
        $this->name = $v['db'];
        $this->prefix = $v['prefix'];

        $xdebug = ini_get ('xdebug.halt_level');
        if ($xdebug !== FALSE) {
            ini_set ('xdebug.halt_level', 0);
        }

        if (isset ($v['port'])) {
            $this->connection = @mysqli_connect ($v['host'], $v['user'], $v['password'], $v['db'], $v['port']);
        } else {
            $this->connection = @mysqli_connect ($v['host'], $v['user'], $v['password'], $v['db']);
        }

        if ($xdebug !== FALSE) {
            ini_set ('xdebug.halt_level', $xdebug);
        }

        if ($this->connection) {
            @mysqli_set_charset ($this->connection, "utf8");
            return;
        }

        error_log ("ERROR: Failed to connect to DB server: " . $v['host']);
    }

    die(json_encode(array('error' => 'Unable to connect to DB')));
    }
    
   
    
    function c($c)
    {
        echo '<pre>'.htmlspecialchars(print_r($c, true)).'</pre>';
        
    }
    function q($query)
    {
        //echo'<pre>'.$query.'</pre>';
        $this->qCount++;
        $this->affectedRows = $this->lastId = null;
        $raytime=microtime(true);
                $this->lastResult = mysqli_query($this->connection, $query);
        //isset($_GET['mysqldebug321']) && error_log ("\n\n#\t".(microtime(true) - $raytime)."\n\n".$query, 3, '/mnt/wwwvideo2dev/log/DB.log');
        
        $e = new Exception();
        
        if((microtime(true) - $raytime) > 0.1) {
            if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'video2dev.darbvedis.lv'):
                error_log ("\n\n#\t".$_SERVER['REQUEST_URI']." / ".getmypid()."\n#\ttime: ".number_format((microtime(true) - $raytime),5)."\n#\tstacktrace: ".$e->getTraceAsString()."\n".$query, 3, '/mnt/wwwvideo2dev/log/DB.log');
            elseif(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'test2.tvdom.tv'):
                error_log ("\n\n#\t".$_SERVER['REQUEST_URI']." / ".getmypid()."\n#\ttime: ".number_format((microtime(true) - $raytime),5)."\n#\tstacktrace: ".$e->getTraceAsString()."\n".$query, 3, '/home/prodwww/php.tmp/DB.log');
            endif;
        }
        
        $this->affectedRows = mysqli_affected_rows($this->connection);
        if (preg_match("/^INSERT/", trim($query))) {
            $this->lastId = mysqli_insert_id($this->connection);
        }
        return $this->lastResult;
    }
    function getVar()
    {
        $numargs = func_num_args();
        $arg_list = func_get_args();
        if ($numargs == 0) {
            return null;
        } else if ($numargs == 1) {
            $this->q($arg_list[0]);
        } else {
            $args = array();
            $query = $arg_list[0];
            unset($arg_list[0]);
            foreach ($arg_list as $arg) {
                if (is_string($arg) && !is_numeric($arg)) {
                    $args[] = $arg;
                } else {
                    $args[] = $arg;
                }
            }
            $this->q(vsprintf($query, $args));
        }

        $myres = $this->rows();
        if ($myres) {
            $myres = $this->fetch();
            $myres = reset($myres);
        } else {
            $myres = null;
        }

        return $myres;

//      return ($this->rows() ? reset($this->fetch()) : null);
    }
    function get_var()
    {
        return call_user_func_array(array($this,"getVar"), func_get_args());
    }
    function queryf()
    {
        $func_num_args = func_num_args();
        $func_get_args = func_get_args();
        if ($func_num_args == 0) {
            return null;
        } else if ($func_num_args == 1) {
            return $this->q($func_get_args[0]);
        } else {
            $args = array();
            $query = $func_get_args[0];
            unset($func_get_args[0]);
            foreach ($func_get_args as $arg) {
                if (is_string($arg) && !is_numeric($arg)) {
                    $args[] = $this->escape($arg);
                } else {
                    $args[] = $arg;
                }
            }
            return $this->q(vsprintf($query, $args));
        }
    }

    function fetch($result = null, $type = MYSQLI_ASSOC)
    {
        $row = NULL;
        if (is_null($result)) {
            $result = $this->lastResult;
        }
        if($result) {
            $row = mysqli_fetch_array($result, $type);
        }
        if (!is_array($row)) {
            return NULL;
        }
        if ($this->dostripslashes) {
            foreach ($row as $key => $val) {
                $row[$key]=$val; //stripslashes(
            }
        }
        return $row;
    }
    
    function getRows()
    {
        $func_num_args = func_num_args();
        $func_get_args = func_get_args();
        if ($func_num_args == 0) {
            return null;
        } else if ($func_num_args == 1) {
            $this->q($func_get_args[0]);
        } else {
            $args = array();
            $query = $func_get_args[0];
            unset($func_get_args[0]);
            foreach ($func_get_args as $arg) {
                if (is_string($arg) && !is_numeric($arg)) {
                    $args[] = $this->escape($arg);
                } else {
                    $args[] = $arg;
                }
            }
            $this->q(vsprintf($query, $args));
        }
        $return = array();
        while ($r = $this->fetch()) {
            $return[]=$r;
        }
        return $return;
    }
    function getRow()
    {
        $func_num_args = func_num_args();
        $func_get_args = func_get_args();
        if ($func_num_args == 0) {
            return null;
        } else if ($func_num_args == 1) {
            $this->q($func_get_args[0]);
        } else {
            $args = array();
            $query = $func_get_args[0];
            unset($func_get_args[0]);
            foreach ($func_get_args as $arg) {
                if (is_string($arg) && !is_numeric($arg)) {
                    $args[] = $this->escape($arg);
                } else {
                    $args[] = $arg;
                }
            }
            $this->q(vsprintf($query, $args));
        }
        return ($this->rows() ? $this->fetch() : null);
    }
    
    function rows($result = null)
    {
        $rc = false;
        if (is_null($result)) {
            $result = $this->lastResult;
        }
        if($result) {
            $rc = mysqli_num_rows($result);
        }
        return $rc;
    }
    
    function table($str)
    {
        if (preg_match("#`(.*)`\.`.*`#", $str, $m) && $m[1]==$this->name) {
            return $str;
        }
        return "`{$this->name}`.`{$this->prefix}{$str}`";
    }
    
    function update($table, $values, $where = NULL) {
            $x__ = array_keys($values);
            if (!is_string($x__[0])) return null;
            else {
                $sql = array();
                foreach ($values as $key => $val) {
                    if (is_string($val) && !is_numeric($val) && !($val == 'NULL' || $val == 'null')):
                        $val = $this->escape($val);
                        $sql[] = "`{$key}`='{$val}'";
                    elseif(is_string($val) && ($val == 'NULL' || $val == 'null')):
                        $sql[] = "`{$key}`= NULL";
                    else:
                        $val = $val;
                        $sql[] = "`{$key}`='{$val}'";
                    endif;
                }
                if (is_array($where)) {
                    foreach ($where as $key => $val) {
                        if (is_string($val) && !is_numeric($val)) $val = $this->escape($val);
                        $where_q[] = "`{$key}`='{$val}'";
                    }
                    $where = join(" AND ",$where_q);
                    unset($where_q);
                }
                $sql = "UPDATE ".$this->table($table)." SET ".join(",",$sql).(!is_null($where) ? " WHERE {$where}" : "");
                return $this->q($sql);
            }
     }

    function insert($table, $values, $update_on_duplicate = false)
    {
        $array_keys = array_keys($values);
        if (!is_string($array_keys[0])) {
            return null;
        } else {
            $sql = array();
            foreach ($values as $key => $val) {
                if (is_string($val) && !is_numeric($val) && !($val == 'NULL' || $val == 'null')):
                    $val = $this->escape($val);
                    $sql[] = "`{$key}`='{$val}'";
                elseif(is_null($val) || (is_string($val) && ($val == 'NULL' || $val == 'null'))):
                    $sql[] = "`{$key}`= NULL";
                else:
                    $sql[] = "`{$key}`='{$val}'";
                endif;
            }
            $sql = "INSERT INTO ".$this->table($table)." SET ".join(",", $sql).($update_on_duplicate ? "ON DUPLICATE KEY UPDATE ".join(",", $sql) : "");
            return $this->q($sql);
            $this->lastId = mysqli_insert_id($this->connection);
        }
    }
    
    function getOpt($var) {
        $d = $this->getRow(
            "SELECT `value` FROM %s WHERE `key`='%s'",
            $this->table('options'),
            strtolower($var)
        );
        $rc = $d ? $d['value'] : false;
        return $rc;
    }
    
    function setOpt($key, $val) {
        $this->insert("options", array(
                "key" => strtolower($key),
                "value" => $val
            ), true);
    }

    function escape($str) {
        return mysqli_real_escape_string($this->connection, $str);
    }
    
    function __destruct()
    {
        @mysqli_close($this->connection);
    }
}