<?php

/*
 * Example PHP implementation used for the REST 'create' interface.
 */

include( "browsers.php" );

echo json_encode( $editor->process($_POST) );

