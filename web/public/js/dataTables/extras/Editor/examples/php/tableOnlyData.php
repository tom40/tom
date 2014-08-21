<?php

/*
 * Example PHP implementation used for the index.html example
 */

include( "include/db.php" );

$editor = new DTEditor(
	$db,        // DB resource
	'users',    // DB table
	'id',       // Primary key
	'row_',     // ID prefix to add to the TR rows (makes it valid and unique)
	array(      // Fields
		new DTField( array(
			"name" => "first_name",
			"dbField" => "first_name"
		) ),
		new DTField( array(
			"name" => "last_name",
			"dbField" => "last_name"
		) ),
		new DTField( array(
			"name" => "updated_date",
			"dbField" => "updated_date",
			"set" => false,
			"dynamicGet" => true,
			"getFormatter" => 'DTFormat::date_mysql_to_format',
			"opts" => array(
				"dateFormat" => 'D, jS F Y'
			)
		) )
	)
);

// The "process" method will handle data get, create, edit and delete 
// requests from the client
echo json_encode( $editor->process($_POST) );

