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
			"validator" => 'DTValidate::date_format',
			"getFormatter" => 'DTFormat::date_mysql_to_format',
			"setFormatter" => 'DTFormat::date_format_to_mysql',
			"opts" => array(
				"dateFormat" => DTFormat::DATE_ISO_8601,
				"dateError" => "Please enter a date in the format yyyy-mm-dd",
				"required" => true
			)
		) ),
		new DTField( array(
			"name" => "registered_date",
			"dbField" => "registered_date",
			"validator" => 'DTValidate::date_format',
			"getFormatter" => 'DTFormat::date_mysql_to_format',
			"setFormatter" => 'DTFormat::date_format_to_mysql',
			"opts" => array(
				"dateFormat" => 'D, d M y'
			)
		) )
	)
);

// The "process" method will handle data get, create, edit and delete 
// requests from the client
echo json_encode( $editor->process($_POST) );

