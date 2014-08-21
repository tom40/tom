<?php

/*
 * Example PHP implementation used for the REST example.
 * This file defines a DTEditor class instance which can then be used, as
 * required, by the CRUD actions.
 */

include( "../include/db.php" );

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

