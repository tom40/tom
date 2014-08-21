<?php

/*
 * Example PHP implementation for the client-side table formatting example.
 * This is basically the same as the 'fieldTypes' example, but in this case
 * note that there is no server-side formatting of the 'done' field - rather it
 * is done in the DataTable in this example
 */

include( "include/db.php" );

$editor = new DTEditor(
	$db,        // DB resource
	'todo',     // DB table
	'id',       // Primary key
	'row_',     // ID prefix to add to the TR rows (makes it valid and unique)
	array(      // Fields
		new DTField( array(
			"name" => "item",
			"dbField" => "item"
		) ),
		new DTField( array(
			"name" => "done",
			"dbField" => "done"
		) ),
		new DTField( array(
			"name" => "priority",
			"dbField" => "priority"
		) )
	)
);

// The "process" method will handle data get, create, edit and delete 
// requests from the client
echo json_encode( $editor->process($_POST) );

