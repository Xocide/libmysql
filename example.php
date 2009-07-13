<?php
/**
 * LibMySQL Example
 * LibMySQL Copyright (c) 2009 Jack Polgar
 */

/**
 * Connecting to the Database
 * The connection is as follows:
 * new MySQL(server,username,password)
 */
include('mysql.class.php');
$db = new MySQL('localhost','root','root');

/**
 * Inserting Data
 *
 * Table layout:
 * ---------------------------
 * | id   | name   | password |
 * ---------------------------
 */
$data = array(
	'name' => 'Indiana Jones',
	'password' => sha1('ihatesnakes');
	);
$db->insert('users',$data);

/**
 * Selecting data
 * Same table.
 */
$args = array(
	'orderby' => 'id ASC',
	'limit' => 50,
	'where' => array(
		'name' => 'Indiana Jones'
		)
	);
$results = $db->select('users',$args);

/**
 * Deleting data
 * Same table.
 */
$args = array(
	'limit' => 1,
	'where' => array(
		'name' => 'Indiana Jones'
		)
	);
$results = $db->delete('users',$args);
?>