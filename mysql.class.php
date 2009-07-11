<?php
/**
 * LibMySQL
 * Copyright (c) 2009 Jack Polgar
 * All Rights Reserved
 */

class MySQL
{
	private $link = NULL;
	private $last_query = NULL;
	public $query_count = 0;
	
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
			$this->connect($server,$user,$pass);
			$this->selectdb($dbname);
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
		$this->last_query = $query;
		$this->query_count++;
		return $result;
	}
	
	/**
	 * Fetch Array
	 * Returns an array that corresponds to the fetched row.
	 */
	public function fetcharray($result)
	{
		$result = mysql_fetch_array($result); // or $this->halt();
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
		$fields = array();
		$getdefaults = $this->query("SHOW COLUMNS FROM ".$this->prefix.$table);
		while($info = $this->fetcharray($getdefaults)) {
			if(isset($data[$info['Field']])) {
				if($info['Type'] == 'date'
				or $info['Type'] == 'datetime'
				or $info['Type'] == 'time')
					$fields[$info['Field']] = "'".$this->res($data[$info['Field']])."'";
				else
					$fields[$info['Field']] = "'".$this->res($data[$info['Field']])."'";
			} else {
				if($info['Type'] == 'date'
				or $info['Type'] == 'datetime'
				or $info['Type'] == 'time')
					$fields[$info['Field']] = $this->res('NOW()');
				elseif(substr($info['Type'],0,6) == 'bigint'
				or substr($info['Type'],0,8) == 'smallint')
					$fields[$info['Field']] = $this->res('NULL');
				else
					$fields[$info['Field']] = "'".$this->res($info['Default'])."'";
			}
		}
		$this->query("INSERT INTO ".$this->prefix.$table." VALUES(".implode(', ',$fields).")");
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