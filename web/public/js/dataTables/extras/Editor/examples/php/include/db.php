<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Database user / pass
 */
$sql_details = array(
	"user" => "root",
	"pass" => "chypons",
	"host" => "localhost",
	"db" => "datatables"
);


// This is included for the development and deploy environment used on the DataTables
// server. You can delete this block - it just includes my own user/pass without making 
// them public!
if ( is_file($_SERVER['DOCUMENT_ROOT']."/datatables/pdo.php") ) {
	include( $_SERVER['DOCUMENT_ROOT']."/datatables/pdo.php" );
}
// /End development include


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Database connection
 */

/* PDO connection */
/*
$db = new PDO(
	"mysql:host={$sql_details['host']};dbname={$sql_details['db']}",
	$sql_details['user'],
	$sql_details['pass'],
	array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	)
);
*/

/* mysql_* connection */
if ( ! $db = mysql_pconnect( $sql_details['host'], $sql_details['user'], $sql_details['pass'] ) ) {
	header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
	die( 'Could not open connection to server' );
}

if ( ! mysql_select_db( $sql_details['db'], $db ) ) {
	header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
	die( 'Could not select database' );
}



/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Include DTEditor class
 */

// Include the DTEditor class that belongs with this connection type
// include( dirname(__FILE__)."/DTEditor.mysql.pdo.class.php" );
//include( dirname(__FILE__)."/DTEditor.postgres.pdo.class.php" );
include( dirname(__FILE__)."/DTEditor.mysql.class.php" );

