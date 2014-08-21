<?php

/*
 * Example PHP implementation used for the index.html example
 */

include( "include/db.php" );

$editor = new DTEditor(
	$db,        // DB resource
	'browsers', // DB table
	'id',       // Primary key
	'row_',     // ID prefix to add to the TR rows (makes it valid and unique)
	array(      // Fields
		new DTField( array(
			"name" => "engine",
			"dbField" => "engine",
			"validator" => "DTValidate::required"
		) ),
		new DTField( array(
			"name" => "browser",
			"dbField" => "browser",
			"validator" => "DTValidate::required"
		) ),
		new DTField( array(
			"name" => "platform",
			"dbField" => "platform"
		) ),
		new DTField( array(
			"name" => "version",
			"dbField" => "version"
		) ),
		new DTField( array(
			"name" => "grade",
			"dbField" => "grade",
			"validator" => "DTValidate::required"
		) )
	)
);

// The "process" method will handle data get, create, edit and delete 
// requests from the client
echo json_encode( $editor->process($_POST) );

