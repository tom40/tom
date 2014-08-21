<?php

/*
 * Example PHP implementation used for the todo list examples
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
			"dbField" => "done",
			// Need to convert between the boolean on the DB and the values for the form
			"setFormatter" => function ($val, $data, $field) {
				return $val == "Done" ? 1 : 0;
			},
			"getFormatter" => function ($val, $data, $field) {
				return $val == 0 ? "To do" : "Done";
			}
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

