<?php
/**
 * LibMySQL
 * Copyright (c) 2009 Jack Polgar
 * All Rights Reserved
 */

class MySQL
{
	private $link = NULL; // Server link
	private $last_query = NULL; // Last query run
	public $query_count = 0; // Query count
	
	/**
	 * Construct
	 * Easily connect to the database.
	 * @param string $server Server to connect to.
	 * @param string $user The username to connect as.
	 * @param string $pass The password for the user.
	 */
	public function __construct($server='',$user='',$pass='',$dbname='')
	{
		if(!empty($server))
		{
			$this->connect($server,$user,$pass); // Connect to the server
			$this->selectdb($dbname); // Select the database
		}
	}
	
	/**
	 * Destruct.
	 * Auto-close the connection.
	 */
	public function __destruct()
	{
		$this->close();
	}
	
	/**
	 * Connect
	 * Connect to the MySQL server.
	 * @param string $server Server to connect to.
	 * @param string $user The username to connect as.
	 * @param string $pass The password for the user.
	*/
	public function connect($server,$user,$pass)
	{
		$this->link = mysql_connect($server,$user,$pass) or $this->halt();
	}
	
	/**
	 * Close connection
	 * Used to close the connection to the server.
	 */
	public function close()
	{
		mysql_close($this->link);
	}
	
	/**
	 * Select Database
	 * Select the database to use.
	 */
	public function selectdb($dbname)
	{
		mysql_select_db($dbname,$this->link);
	}
	
	/**
	 * Query
	 * Query the selected Database.
	 * @param string $query The query to run.
	 */
	public function query($query)
	{
		$result = mysql_query($query,$this->link) or $this->halt($query);
		$this->last_query = $query; // Set the last query run
		$this->query_count++; // Update query count
		return $result;
	}
	
	/**
	 * Fetch Array
	 * Returns an array that corresponds to the fetched row.
	 */
	public function fetcharray($result)
	{
		$result = mysql_fetch_array($result);
		return $result;
	}
	
	/**
	 * Escape String
	 * Escapes a string for use in a query.
	 * @param string $string String to escape.
	 * @return string
	 */
	public function escapestring($string)
	{
		return mysql_escape_string($string);
	}
	
	/**
	 * Escape String short cut.
	 */
	public function es($string)
	{
		return $this->escapestring($string);
	}
	
	/**
	 * Real Escape String
	 * Escapes special characters from a string for use in a query.
	 * @param string $string String to escape.
	 * @return string
	 */
	public function realescapestring($string)
	{
		return mysql_real_escape_string($string);
	}
	
	/**
	 * Real Escape String short cut.
	 */
	public function res($string)
	{
		return $this->realescapestring($string);
	}
	
	/**
	 * Num Rows
	 * Get number of rows in result.
	 */
	public function numrows($result)
	{
		return mysql_num_rows($result);
	}
	
	/**
	 * Query First
	 * Query and fetch the array of the first row returned.
	 */
	public function queryfirst($query)
	{
		return $this->fetcharray($this->query($query));
	}
	
	/**
	 * Insert
	 * Easily insert data into a table.
	 * @param string $table The table to insert data into.
	 * @param array $data Array filled with column data.
	 */
	public function insert($table,$data)
	{
		// Get the table columns
		$fields = array();
		$getdefaults = $this->query("SHOW COLUMNS FROM ".$this->prefix.$table);
		while($info = $this->fetcharray($getdefaults)) {
			// Use the specified column value.
			if(isset($data[$info['Field']])) {
				if($info['Type'] == 'date'
				or $info['Type'] == 'datetime'
				or $info['Type'] == 'time')
					$fields[$info['Field']] = "'".$this->res($data[$info['Field']])."'"; // Time column
				else
					$fields[$info['Field']] = "'".$this->res($data[$info['Field']])."'"; // Other column
			} else {
			// Use either the Default value or the value best used for the column type.
				if($info['Type'] == 'date'
				or $info['Type'] == 'datetime'
				or $info['Type'] == 'time')
					$fields[$info['Field']] = $this->res('NOW()'); // Time column
				elseif(substr($info['Type'],0,6) == 'bigint'
				or substr($info['Type'],0,8) == 'smallint')
					$fields[$info['Field']] = $this->res('NULL'); // Integer column
				else
					$fields[$info['Field']] = "'".$this->res($info['Default'])."'"; // Other
			}
		}
		$this->query("INSERT INTO ".$this->prefix.$table." VALUES(".implode(', ',$fields).")");
	}
	
	/**
	 * Select
	 * Easily execute a select query.
	 */
	public function select($table,$args)
	{
		$query = 'SELECT * FROM '.$table.' ';
		
		$orderby = (isset($args['orderby']) ? " ORDER BY ".$args['orderby'] : NULL);
		unset($args['orderby']);
		
		$limit = (isset($args['limit']) ? ' LIMIT '.$args['limit'] : NULL);
		unset($args['limit']);
		
		if(is_array($args['where'])) {
			$fields = array();
			foreach($args['where'] as $field => $value)
			{
				$fields[] = $field."='".$value."'";
			}
			$fields = ' WHERE '.implode(' AND ',$fields);
		} else {
			$fields = $args['where'];
		}
		
		$query .= $fields;
		$query .= $orderby;
		$query .= $limit;
		
		return $this->query($query);
	}
	
	/**
	 * Delete
	 * Easily execute a delete query.
	 */
	public function delete($table,$args)
	{
		$query = 'DELETE FROM '.$table.' ';
		
		$limit = (isset($args['limit']) ? ' LIMIT '.$args['limit'] : NULL);
		unset($args['limit']);
		
		if(is_array($args['where'])) {
			$fields = array();
			foreach($args['where'] as $field => $value)
			{
				$fields[] = $field."='".$value."'";
			}
			$fields = ' WHERE '.implode(' AND ',$fields);
		} else {
			$fields = ' WHERE '.$args['where'];
		}
		
		$query .= $fields;
		$query .= $limit;
		
		$this->query($query);
	}
	
	/**
	 * Get Fields
	 * Get the fields of the specified table.
	 * @param string $table Table name.
	 * @return array
	 */
	public function getfields($table) {
		$fields = array();
		$fetch = $this->query("SHOW COLUMNS FROM ".$this->prefix.$table);
		while($info = $this->fetcharray($fetch)) {
			$fields[$info['Field']] = $info['Default'];
		}
		return $fields;
	}
	
	// MySQL Error Number
	private function errno()
	{
		return mysql_errno($this->link);
	}
	
	// MySQL Error
	private function error()
	{
		return mysql_error($this->link);
	}
	
	// The halt function. used to display errors..
	private function halt()
	{
		print("<blockquote style=\"border:2px solid darkred;padding:5px;background:#f9f9f9;font-family:arial; font-size: 14px;\">");
		print("<h1 style=\"margin:0px;color:#000;border-bottom:1px solid #000;margin-bottom:10px;\">Database Error</h1>");
		print("<div style=\"padding: 0;\">".'#'.$this->errno().': '.$this->error()."</div>");
		print("</blockquote>");
		exit;
	}
}
?>