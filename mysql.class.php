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
	private $query_count = 0;
	
	/**
	 * Construct
	 * Easily connect to the database.
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
	*/
	public function connect($server,$user,$pass)
	{
		$this->link = mysql_connect($server,$user,$pass) or $this->halt();
	}
	
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