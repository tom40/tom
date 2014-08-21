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

// If getting data for the table, manipulate the output so it contains nested
// objects for the deepObjects example.
//
// You typically wouldn't do this here, but we do so for the deepObjects example
// to show how the client-side can cope with nested objects
if ( !isset($_POST['action']) ) {
	$out = array();
	$data = $editor->process($_POST);
	$data = $data['aaData'];

	for ( $i=0 ; $i<count($data) ; $i++ ) {
		$out[] = array(
			"DT_RowId" => $data[$i]['DT_RowId'],
			"engine" => $data[$i]['engine'],
			"browser" => $data[$i]['browser'],
			"details" => array(
				"platform" => $data[$i]['platform'],
				"version" => $data[$i]['version'],
				"grade" => $data[$i]['grade']
			)
		);
	}

	echo json_encode( array(
		"aaData" => $out
	) );
}
else {
	// create, edit or remove
	echo json_encode( $editor->process($_POST) );
}

