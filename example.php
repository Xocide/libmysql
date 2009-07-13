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
$db->selectdb('mydatabase');

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
// then use the normal while() loop with $db->fetcharray($results);

/**
 * Selecting data, a less easier way.
 * Same table.
 */
$args = array(
	'orderby' => 'is ASC',
	'limit' => 50,
	'where' => "name='Indiana Jones' OR name='Daniel Jackson' or name LIKE '% Carter'"
	);
$db->select('users',$args);
// Works the same for delete();

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

/**
 * Selecing data the old fashioned way
 * Same table
 */
$results = $db->query("SELECT * FROM users WHERE name='Indiana Jones' ORDER BY id DESC LIMIT 50");
// then use the normal while() loop with $db->fetcharray($results);
?>