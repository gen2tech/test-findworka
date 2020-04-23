<?php
namespace App;

Class Sqlite extends \SQLite3 {
    private $sqlite;
    private $mode;
    static $inst = null;
    public static $counter = 0;

    function __construct( $filename, $mode = SQLITE3_ASSOC)
    {
        $this->mode = $mode;
        if(!$this->open($filename)){
            return $this->lastErrorMsg();
        }
	}
	
	 /**
     * Singleton function
     *
     * Example usage:
     * $database = Sqlite::getInstance();
     *
     * @access private
     * @return self
     */
    static function getInstance($dbFile){
        if( self::$inst == null ){
            self::$inst = new Sqlite($dbFile);
        }
        return self::$inst;
    }

    function __destruct(){
        @$this->close();
    }

    /**
     * Quote and escape value for use in a database query
     *
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     */
    function quote( $str ){
        if(!is_array( $str  )){
            $data =  $this->escapeString( $str );
        }else{
            //Self call function to sanitize array data
            $data = array_map( array( $this, 'quote' ), $str );
        }
        return $data;
    }

    /**
     * Sanitize user data
     *
     * Example usage:
     * $user_name = $database->filter( "Jam'es Jones" ); should be turned to "Jam\'es Jones"
     *
     * Or to filter an entire array:
     * $str = array( 'name' => "Jam'es Jones", 'email' => 'email@address.com' );
     * $str = $database->filter( $data );
     *
     * @access public
     * @param mixed $str
     * @return mixed $str
     */
    public function filter($str) {
        if (!is_array($str)) {
            $str = $this->escapeString($str);
            $str = trim(htmlentities($str, ENT_QUOTES, 'UTF-8', false));
        } else {
            //Self call function to sanitize array data
            $str = array_map(array($this, 'filter'), $str);
        }
        return $str;
    }

    /**
     * Normalize sanitized data for display (reverse $database->filter cleaning)
     *
     * Example usage:
     * echo $database->clean("Jam\'es Jones"); should be back to "Jam'es Jones"
     *
     * @access public
     * @param string $str
     * @return string $str
     */
    public function clean($str) {
        if (!is_array($str)) {
            $str = stripslashes($str);
            $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
            $str = nl2br($str);
            $str = urldecode($str);
        } else {
            //Self call function to Normalize sanitized array data
            $str = array_map(array($this, 'clean'), $str);
        }
        return $str;
    }

    /**
     *  Proccess sql queries
     *
     * @param       The query to proccess
     * @multiple    if it is multiple queries Todo: find a way to do multiple query because as of now Sqlite3 does not support it
     * @return      Returns the result of the query
     */
    function processSql( $query, $multiple=false){
        $res = $this->query( $query );
        if ( !$res ){
            throw new Exception( $this->lastErrorMsg() );
        }
        return $res;
    }

    /**
     *  Proccess sql queries like method "query" only that it executes result-less querys
     *
     * @param       The query to proccess
     * @return      Returns the result of the query
     */
    function execute( $query ){
        $res = $this->exec( $query );
        if ( !$res ){
            throw new \Exception( $this->lastErrorMsg() );
        }else{
			return true;
		}
    }

    /**
     * Fetch a row from the database (SELECT query)
     *
     * @param $query The query string
     * @return bool False on failure / array Database rows on success
     */
    function fetchOneRow( $query ){
        $res = $this->processSql( $query );
        $row = $res->fetchArray( $this->mode );
        return $row;
    }

    /**
     * Fetch rows from the database (SELECT query)
     *
     * @param $query The query string
     * @return bool False on failure / array Database rows on success
     */
    function fetchAllRows( $query ){
        $rows = array();
        if( $res = $this->processSql( $query ) ){
            while($row = $res->fetchArray($this->mode)){
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /**
     *  Get Num of Rows in a result
     *
     * @param $query The query string
     * @return Number of Rows in a result
     */
    public function resultNumOfRows($query){
        $results = $this->fetchOneRow("SELECT COUNT(*) AS `count` FROM ($query);");
        $results['count'];
    }


    /**
     * Count number of fields/column in a result
     *
     * @param mixed $query Select query or MySQL result
     * @return int Number of fields
     */
    public function resultNumOfFields($query){
        $res = $this->processSql( $query );
        return $res->numColumns();
    }



    /**
     * Get last inserted id of the last query
     */
    function lastid(){
        return $this->lastInsertRowID();
	}
	

	/**
     * Insert data into database table
     *
     * Example usage:
     * $user_data = array(
     *      'name' => 'Bennett', 
     *      'email' => 'email@address.com', 
     *      'active' => 1
     * );
     * $database->insert( 'users_table', $user_data );
     *
     * @access public
     * @param string table name
     * @param array table column => column value
     * @return bool
     *
     */
    public function insert( $table, $variables = array() ){
        //Make sure the array isn't empty
        if( empty( $variables ) ){
            return false;
        }
        
        $sql = "INSERT INTO ". $table;
        $fields = array();
        $values = array();
        foreach( $variables as $field => $value ){
            $fields[] = $field;
            $values[] = "'".$value."'";
        }
        $fields = ' (' . implode(', ', $fields) . ')';
        $values = '('. implode(', ', $values) .')';
        
        $sql .= $fields .' VALUES '. $values;

		$query = $this->execute( $sql );
        
        if(!$query){
            return false;
        }else{
            return true;
        }
	}


	
    /**
     * Update data in database table
     *
     * Example usage:
     * $update = array( 'name' => 'Not bennett', 'email' => 'someotheremail@email.com' );
     * $update_where = array( 'user_id' => 44, 'name' => 'Bennett' );
     * $database->update( 'users_table', $update, $update_where, 1 );
     *
     * @access public
     * @param string table name
     * @param array values to update table column => column value
     * @param array where parameters table column => column value
     * @param int limit
     * @return bool
     *
     */
    public function update( $table, $variables = array(), $where = array(), $limit = '' ){
        //Make sure the required data is passed before continuing
        //This does not include the $where variable as (though infrequently)
        //queries are designated to update entire tables
        if( empty( $variables ) ){
            return false;
        }
        $sql = "UPDATE ". $table ." SET ";
        foreach( $variables as $field => $value ){
            
            $updates[] = "`$field` = '$value'";
        }
        $sql .= implode(', ', $updates);
        
        //Add the $where clauses as needed
        if( !empty( $where ) ){
            foreach( $where as $field => $value ){
                $value = $value;

                $clause[] = "$field = '$value'";
            }
            $sql .= ' WHERE '. implode(' AND ', $clause);   
        }
		
		//That query works only if you have compiled SQLite with SQLITE_ENABLE_UPDATE_DELETE_LIMIT.
        /* if( !empty( $limit ) ){
            $sql .= ' LIMIT '. $limit;
        } */
        
        $query = $this->execute( $sql );
        
        if(!$query){
            return false;
        }else{
            return true;
        }
	}
	

	
    /**
     * Delete data from table
     *
     * Example usage:
     * $where = array( 'user_id' => 44, 'email' => 'someotheremail@email.com' );
     * $database->delete( 'users_table', $where, 1 );
     *
     * @access public
     * @param string table name
     * @param array where parameters table column => column value
     * @param int max number of rows to remove.
     * @return bool
     *
     */
    public function delete( $table, $where = array(), $limit = '' ){
        //Delete clauses require a where param, otherwise use "truncate"
        if( empty( $where ) ){
            return false;
        }
        
        $sql = "DELETE FROM ". $table;
        foreach( $where as $field => $value ){
            $value = $value;
            $clause[] = "$field = '$value'";
        }
        $sql .= " WHERE ". implode(' AND ', $clause);
		
		
		//That query works only if you have compiled SQLite with SQLITE_ENABLE_UPDATE_DELETE_LIMIT.
        /* if( !empty( $limit ) ){
            $sql .= " LIMIT ". $limit;
        } */
            
        $query = $this->execute( $sql );
        
        if(!$query){
            return false;
        }else{
            return true;
        }
    }



	
	/**
     * Determine if database table exists
     * Example usage:
     * if( !$database->table_exists( 'checkingfortable' ) )
     * {
     *      //Install your table or throw error
     * }
     *
     * @access public
     * @param string
     * @return bool
     *
     */
	public function table_exists( $name ){
		$tableCheck =$this->query("SELECT name FROM sqlite_master WHERE name='$name'");
		  if ($tableCheck->fetchArray() === false){
			return false;
		  }else{
			return true;
		  }

		/* $check = $this->query( "SELECT 1 FROM $name" );
		if($check !== false){
			if( $check->num_rows > 0 ){
				return true;
			}
			else{
				return false;
			}
		}else{
			return false;
		} */
	}

}
?>