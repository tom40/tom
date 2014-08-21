<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

if (isset($_SERVER['ENVIRONMENT']))
{
    define('APPLICATION_ENV', $_SERVER['ENVIRONMENT']);
}
elseif (isset($_SERVER['APP_ENVIRONMENT']))
{
    define('APPLICATION_ENV', $_SERVER['APP_ENVIRONMENT']);
}
elseif (file_exists(APPLICATION_PATH . '/../tools/env/env'))
{
    $envFile = file(APPLICATION_PATH . '/../tools/env/env');
    define('APPLICATION_ENV', $envFile[0]);
}
else
{
	// If environment not set then use the host name
	$host = !empty($_SERVER['HTTP_HOST'])? $_SERVER['HTTP_HOST']: null;
	$environment = null;
	
	switch($host)
	{
		case 'takenote.localhost':
			$environment = "development";
			break;
			
		case 'test-takenotetyping.co.uk':
			$environment = 'testing';
			break;
			
		case 'live.test-takenotetyping.co.uk':
		case 'portal.takenotetyping.com':
		default:
			$environment = 'production';
	}
	
	define('APPLICATION_ENV', $environment);
}

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();