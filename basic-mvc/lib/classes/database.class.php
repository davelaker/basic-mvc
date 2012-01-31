<?php
/**
 * Database connection and queries
 *
 * @author DaveLaker
 */
class Database {

    private $connection;
    private $database;
    private $numQueries = 0;
    private $queries = array();
    private $lastResult;
    private $lastQueryExecution;
    private $totalQueryExecution;
    private $objStart;
    private $defaultDebug = true;
    
    private static $instance;

    /**
     * constructor for db class
     *
     */
    private function __construct() {
        $this->objStart = $this->getMicroTime();
        $this->dbConnect();
    }
    
    
    /**
     * Used for Singleton Design pattern so we don't have institate for every model
     * 
     * @return object
     */
    public static function getInstance() {
        
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
        
    }

    /**
     * performs actual query
     *
     * @param $sql string the query to execute
     * @param $bebug bool whether or not to debug
     * @return db resource
     */
    private function _processQuery($sql) {
        
        $this->numQueries++;
        $sqlStart = $this->getMicroTime();
        $result = mysql_query($sql) or $this->debugAndDie($sql);
        $sqlEnd = $this->getMicroTime();
        $this->lastQueryExecution = $sqlEnd - $sqlStart;
        $this->totalQueryExecution += $this->lastQueryExecution;
        
        
        $this->queries[] = array(
            'start' => $sqlStart,
            'end' => $sqlEnd,
            'time' => $sqlEnd - $sqlStart,
            'query' => $sql,
        );
        
        return $result;
    }

    /**
     * processes query and debugs if required
     *
     * @param $sql string the query to execute
     * @param $bebug bool whether or not to debug
     * @return db resource
     */
    public function doQuery($sql, $debug=false) {
        
        $this->lastResult = $this->_processQuery($sql);
        $this->debug($debug, $sql, $this->lastResult);

        return $this->lastResult;
    }

    /**
     * Do the same as doQuery() but do not return result.
     * Should be used for INSERT, UPDATE, DELETE...
     * NOTE: doesn't store result in $this->lastResult;
     *
     * @param $query The query.
     * @param $debug If true, it output the query and the resulting table.
     */
    function doExecute($sql, $debug=false) {
      
        $this->_processQuery($sql);
        $this->debug($debug, $sql);
        
    }

    /**
     * Get the result of the query as value
     * NOTE: doesn't store result in $this->lastResult;
     *
     * @param $sql The query.
     * @param $debug If true, it output the query and the resulting value.
     * @return string The required value.
     */
    function doQuerySingle($sql, $debug = false) {

        $sql = $sql." LIMIT 1";
        $result = $this->_processQuery($sql);
        $count = $this->numRows($result);
        if($count == 0) return false;
        $row = mysql_fetch_row($result);

        $this->debug($debug, $sql, $result);

        return $row[0];
    }

     /**
     * Get the result of the query as entire row
     * NOTE: doesn't store result in $this->lastResult;
     *
     * @param $sql The query.
     * @param $debug If true, it output the query and the resulting value.
     * @return string The required value.
     */
    function doQuerySingleRow($sql, $debug = false) {
        $sql = $sql." LIMIT 1";

        $result = $this->_processQuery($sql);
        if(!$this->numRows($result)) return false;
        $row = mysql_fetch_object($result);

        $this->debug($debug, $sql, $result);

        return $row;
    }

    /**
     * Convenient method for mysql_fetch_object().
     *     *
     * @param $result The ressource returned by query(). If NULL, the last result returned by query() will be used.
     * @return An object representing a data row.
     */
    function fetchNextRow($result = NULL) {
        if ($result == NULL) $result = $this->lastResult;

        if ($result == NULL || mysql_num_rows($result) < 1) return false;
        else return mysql_fetch_object($result);
    }

    /**
     * Get the number of rows of a query.
     *
     * @param $result The ressource returned by doQuery(). If NULL, the last result returned by doQuery() will be used.
     * @return The number of rows of the query (0 or more).
     */
    public function numRows($result = NULL) {
        if ($result == NULL) return mysql_num_rows($this->lastResult);
        else return mysql_num_rows($result);
    }

    /**
     * database connection
     *
     */
    public function dbConnect() {
        
        $this->connection = mysql_connect(Config::read('db_host'), Config::read('db_user'), Config::read('db_pass'));
        if(!$this->connection) {
            $messages = array();
            $messages['errno'] = mysql_errno();
            $messages['error'] = mysql_error();
            Core::FatalError('dbConnectionFail', $messages);
        }
        $this->database = mysql_select_db(Config::read('db_name'));
        if(!$this->database) {
            $messages = array();
            $messages['errno'] = mysql_errno();
            $messages['error'] = mysql_error();
            Core::FatalError('dbDatabaseFail', $messages);
        }
        return true;
    }

    /**
     * Debug when MySQL encountered an error, even if debug is set to Off.
     *
     * @param $sql string The SQL query to echo before diying.
     */
    function debugAndDie($sql) {
        
        $this->debugQuery($sql, "Error");
        die("<p style=\"margin: 2px;\">".mysql_error()."</p></div>");
    }

    /**
     * Debug a MySQL query.
     * Show the query and output the resulting table if not NULL.
     *
     * @param $debug bool The parameter passed to query() functions. Can be boolean or -1 (default).
     * @param $sql string The SQL query to debug.
     * @param $result resource The resulting table of the query, if available.
     */
    private function debug($debug, $sql, $result = NULL) {
        
        if ((!$debug) && (!$this->defaultDebug)) return;
        if (!$debug) return;

        $reason = (!$debug) ? "Default Debug" : "Debug";
        $this->debugQuery($sql, $reason);
        if ($result == NULL) echo "<p style=\"margin: 2px;\">Number of affected rows: ".mysql_affected_rows()."</p></div>";
        else $this->debugResult($result);
    }

    /**
     * Internal function to output a query for debug purpose.
     * Should be followed by a call to debugResult() or an echo of "</div>".
     *
     * @param $sql string The SQL query to debug.
     * @param $reason string The reason why this function is called: "Default Debug", "Debug" or "Error".
     */
    private function debugQuery($sql, $reason = "Debug") {
        
        $color = ($reason == "Error" ? "red" : "orange");
        echo "<div style=\"border: solid $color 1px; margin: 2px;\">".
           "<p style=\"margin: 0 0 2px 0; padding: 0; background-color: #DDF;\">".
           "<strong style=\"padding: 0 3px; background-color: $color; color: white;\">$reason:</strong> ".
           "<span style=\"font-family: monospace;\">".htmlentities($sql)."</span></p>";
    }

    /**
     * Internal function to output a table representing the result of a query, for debug purpose.
     * Should be preceded by a call to debugQuery().
     *
     * @param $result The resulting table of the query.
     */
    private function debugResult($result) {
        
        echo "
        <table border=\"1\" style=\"margin: 2px;\">
            <thead style=\"font-size: 80%\">";
        $numFields = mysql_num_fields($result);
        // BEGIN HEADER
        $tables    = array();
        $nbTables  = -1;
        $lastTable = "";
        $fields    = array();
        $nbFields  = -1;
        while ($column = mysql_fetch_field($result)) {
            if ($column->table != $lastTable)
            {
                $nbTables++;
                $tables[$nbTables] = array("name" => $column->table, "count" => 1);
            } else {
                $tables[$nbTables]["count"]++;
                $lastTable = $column->table;
                $nbFields++;
                $fields[$nbFields] = $column->name;
            }
        }
        for ($i = 0; $i <= $nbTables; $i++)
        {
            echo "<th colspan=".$tables[$i]["count"].">".$tables[$i]["name"]."</th>";
        }
        echo "</thead>";
        echo "<thead style=\"font-size: 80%\">";
        for ($i = 0; $i <= $nbFields; $i++)
        {
            echo "<th>".$fields[$i]."</th>";
        }
        echo "</thead>";
        // END HEADER
        while ($row = mysql_fetch_array($result))
        {
            echo "<tr>";
            for ($i = 0; $i < $numFields; $i++)
            {
                echo "<td>".htmlentities($row[$i])."</td>";
            }
            echo "</tr>";
        }
        echo "</table></div>";
        $this->resetFetch($result);
    }

    /**
     * Get how many time the script took from the begin of this object.
     *
     * @return float
     */
    function getExecTime() {
        return round(($this->getMicroTime() - $this->objStart) * 1000) / 1000;
    }

    /**
     * Get the number of queries executed from the begin of this object.
     *
     * @return int
     */
    function getQueriesCount() {
        return $this->numQueries;
    }

    /**
     * Get the number of seconds the queries took to execute
     *
     * @return float
     */
    function getTotalExecutionTime() {
        return round(($this->totalQueryExecution * 1000)) / 1000;
    }

    /**
     * Get all queries executed from the begin of this object.
     *
     * @return array
     */
    function getQueries() {
        return $this->queries;
    }

    /**
     * Go back to the first element of the result line.
     * 
     * @param $result 
     */
    function resetFetch($result) {
        if (mysql_num_rows($result) > 0) mysql_data_seek($result, 0);
    }

    /**
     * Get the id of the very last inserted row.
     *
     * @return int
     */
    function lastInsertedId() {
        return mysql_insert_id();
    }
    
    /**
     * Close the connection with the database server 
     * PHP normally does it automatically at the end of a script.
     * 
     */
    function close() {
        mysql_close();
    }

    /**
     * Get current time in seconds
     * 
     * @return float
     */
    function getMicroTime() {
        
        return microtime(true);
        
        # Below was an alternative way to do things, but it failed very occasionly - codepad.org/OVf9aoOR
        #list($msec, $sec) = explode(' ', microtime());
        #return floor($sec / 1000) + $msec;
    }

    /**
     * Add mysql_real_escape_string to vars in query.
     *
     * @return string
     */
    function mres($val) {
        return mysql_real_escape_string($val);
    }
}