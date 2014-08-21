<?php

/*
 * Example PHP implementation used for the htmlTable.php example
 */

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
include( "include/db.php" );

$stmt = $db->prepare( "
	SELECT id, browser, engine, platform, version, grade
	FROM browsers
" );
$stmt->execute();

while ( $row=$stmt->fetch() ) {
	echo <<<EOD
		<tr id="row_{$row['id']}">
			<td>{$row['browser']}</td>
			<td>{$row['engine']}</td>
			<td>{$row['platform']}</td>
			<td>{$row['version']}</td>
			<td>{$row['grade']}</td>
		</tr>
EOD;
}
