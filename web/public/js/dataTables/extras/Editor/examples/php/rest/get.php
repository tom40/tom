<?php

/*
 * Example PHP implementation used for the REST 'get' interface
 */

include( "browsers.php" );

echo json_encode( $editor->process($_POST) );

