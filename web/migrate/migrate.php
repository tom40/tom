<?php
/**
 * Migration script
 *
 * Booststraps and uses an external Yii application to perform migrations.
 *
 * Allows an extra flag, either -j -r -t to be passed to set the environment to
 * either 'testing' or 'jenkins'.  This is removed before the Yii migration script
 * is called.
 *
 * Allows a database name to be passed -d database_name
 *
 * The additional flags must be stripped from the command before the Yii
 * migrations are run
 *
 * Yii console scripts use $_SERVER['argv'] rather than $argv but both are unset
 * for completeness.
 *
 * Only a standard migrate
 *
 * PHP Version 5.3
 *
 * @category   category
 * @package    package
 * @subpackage subPackage
 * @copyright  Copyright (c) 2011 Sophia Office Ltd. (http://www.sophiaoffice.com)
 * @version    $Id:$
 * @since      1.0
 * @access     private
 */

define( 'DS', DIRECTORY_SEPARATOR );

/**
 * If it exists retrieve the envinronment flag from the command line
 */
$env = 'development';

if( in_array( '-e', $argv ) )
{
    $key = array_search( '-e', $argv );
    $env = $argv[( $key + 1 )];
    unset( $argv[$key], $_SERVER['argv'][$key], $argv[( $key + 1 )], $_SERVER['argv'][( $key + 1 )] );
}

if( isset( $_SERVER['APP_ENVIRONMENT'] ) )
{
    define( 'APPLICATION_ENV', $_SERVER['APP_ENVIRONMENT'] );
}
else
{
    define( 'APPLICATION_ENV', $env );
}

// Define path to application directory
defined( 'APPLICATION_PATH' )
|| define( 'APPLICATION_PATH', realpath( dirname( __FILE__ ) . '/../application' ) );

// Ensure library/ is on include_path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            realpath( APPLICATION_PATH . '/library' ),
            realpath( APPLICATION_PATH . '/../library' ),
            get_include_path(),
        )
    )
);

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap();
$config   = Zend_Registry::get( 'config' );
$dbConfig = $config->db->params;

/**
 * Allow a new database name to be passed to the migration script from the command
 * line
 *
 * This allows for unique database in testing environments
 */
if( in_array( '-d', $argv ) )
{
    $key      = array_search( '-d', $argv );
    $database = $argv[( $key + 1 )];

    unset(
        $argv[$key],
        $_SERVER['argv'][$key],
        $argv[( $key + 1 )],
        $_SERVER['argv'][( $key + 1 )]
    );
}
else
{
    $database = $dbConfig->dbname;
}

define( 'DB_HOST', $dbConfig->host );
define( 'DB_USER', $dbConfig->username );
define( 'DB_DATABSE', $database );
define( 'DB_PASSWORD', $dbConfig->password );

// change the following paths if necessary
$yiic   = $config->migration->framework . 'framework/yiic.php';
$config = dirname( __FILE__ ) . '/config/config.php';

require_once( $yiic );