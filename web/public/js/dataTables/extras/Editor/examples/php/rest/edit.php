<?php

/*
 * Example PHP implementation used for the REST 'create' interface.
 */

include( "browsers.php" );

parse_str( file_get_contents('php://input'), $args );
echo json_encode( $editor->process($args) );

