<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initConfig()
	{

		Zend_Session::start();

	    $config = new Zend_Config($this->getOptions());
	    Zend_Registry::set('config', $config);
	    return $config;
	}

    /**
     * Setup the log.
     *
     * @throws Exception where the error log location is not configured
     *
     * @return Zend_Log
     */
    protected function _initLog()
    {
        // Ensure config instance is present, and fetch it
        $this->bootstrap('config');
        $config = $this->getResource('config');

        if (!($config->app->errors->log)) {
            throw new Exception("Expected 'app.errors.log' key in application.ini does not exist");
        }

        $log = new Zend_Log();
        $log->setTimestampFormat(Zend_Date::RFC_2822);

        // Set up formatter to be used by all writers
        $format = '%timestamp% %priorityName% (%priority%)' . PHP_EOL
            . '%message%' . PHP_EOL;
        $formatter = new Zend_Log_Formatter_Simple($format);

        // Create a file writer
        $writer1 = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../' . $config->app->errors->log);
        $writer1->setFormatter($formatter);
        $log->addWriter($writer1);
        
        if (in_array(APPLICATION_ENV, array('testing','development')))
        {
        	// Add firebug output to prove it works
        	$writer = new Zend_Log_Writer_Firebug();
        	$log->addWriter($writer);
        	$log->log('FireBug logging is enabled!', Zend_Log::INFO);
        }

        return $log;
    }

	/**
	* Setup the autoloader for our own libraries.
	*
	* @return Zend_Loader_Autoloader
	*/
	protected function _initAutoloader()
	{
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace( 'App_' );
		$autoloader->registerNamespace( 'Noumenal_' );
        $autoloader->registerNamespace( 'Html2Pdf' );

		return $autoloader;
	}

	protected function _initView()
	{

		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		if (null === $viewRenderer->view) {
			$viewRenderer->initView();
		}
		$view = $viewRenderer->view;

		$view->addHelperPath('App/View/Helper/', 'App_View_Helper');

		$view->addHelperPath('Noumenal/View/Helper','Noumenal_View_Helper');

        return $view;
    }

    /**
    * Setup front controller
    *
    * @return Zend_Controller_Front
    */
    protected function _initFrontController()
    {

    	$frontController = Zend_Controller_Front::getInstance();

    	$route = new Zend_Controller_Router_Route(
                         ':lang/:module/:controller/:action/*',
    	array(
                             'lang'       => 'en',
                             'module'     => 'default',
                             'controller' => 'index',
                             'action'     => 'index',
    	));
    	$router = $frontController->getRouter();
    	$router->removeDefaultRoutes();
    	$router->addRoute('default', $route);
    	$frontController->setRouter($router);
    	$frontController->throwExceptions(false);
    	$frontController->setControllerDirectory(APPLICATION_PATH . '/controllers');

    	// Register plugins
		// None

    	return $frontController;
    }
	protected function _initDoctype()
	{
		//we will first ensure the View resource has run, fetch the view object, and then configure it:
		$this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }

	protected function _initDb()
	{
    	// Ensure the config is initialized
        $this->bootstrap('config');

        // Retrieve the config from the bootstrap registry
        $config = $this->getResource('config');

        // Configure database and store to the registery
        $db = Zend_Db::factory($config->db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
        
        if (in_array(APPLICATION_ENV, array('testing','development')))
        {       
        	// Enable db profiling to firebug
        	$profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
        	$profiler->setEnabled(true);
        	$db->setProfiler($profiler);
        }        
        
        return $db;
	}

	/**
	*
	*/
	protected function _initAcl()
	{
		// Ensure the front controller is initialized
		$this->bootstrap('frontController');

		// Retrieve the front controller from the bootstrap registry
		$frontController = $this->getResource('frontController');

		if (Zend_Auth::getInstance()->hasIdentity())
        {
			$userId     = Zend_Auth::getInstance()->getIdentity()->id;
			$aclGroupId = Zend_Auth::getInstance()->getIdentity()->acl_group_id;
			$aclRoleId  = Zend_Auth::getInstance()->getIdentity()->acl_role_id;

			require_once 'App/AclFactory.php';
			$factory = new App_AclFactory();
			$acl     = $factory->createAcl($userId, $aclGroupId, $aclRoleId);

            $aclPlugin = new App_Controller_Plugin_Acl($acl);

			// register the acl plugin which will redirect to controller error->deniedAction if request controller.action not found in acl
			$frontController->registerPlugin($aclPlugin);

			// register an action helper which will give access to the acl from within a controller
			require_once 'App/Controller/Action/Helper/Acl.php';
			require_once 'Zend/Controller/Action/HelperBroker.php';
			Zend_Controller_Action_HelperBroker::addHelper(new App_Controller_Action_Helper_Acl($aclPlugin));

            App_View_Helper_Acl::setAclPlugin($aclPlugin);
            App_Db_Table::setAclPlugin($aclPlugin);
		}
	}

	protected function _initEmail()
	{
        // Generate the mail transport.
        switch (APPLICATION_ENV)
        {
            case 'production':
                $transport = new Zend_Mail_Transport_Sendmail();
                break;
            default:
                $transport = new Zend_Mail_Transport_File(array(
                    'path' => realpath(APPLICATION_PATH . '/../cache'),
                    'callback' => array('App_Mail_Mail', 'generateMailFilename')
                ));
                break;
        }

        // Set the default transport.
        App_Mail::setDefaultTransport($transport);

        $config = $this->bootstrap('config')
            ->getResource('config');

        if (!empty($config->app->email->forcedEmailAddress))
        {
            App_Mail::setForcedEmailAddress($config->app->email->forcedEmailAddress);
        }

		// Ensure the front controller is initialized
		$this->bootstrap('frontController');

		// Retrieve the front controller from the bootstrap registry
		$frontController = $this->getResource('frontController');

        require_once 'App/Controller/Action/Helper/Email.php';
		require_once 'Zend/Controller/Action/HelperBroker.php';

        if (in_array(APPLICATION_ENV, array('testing','development')))
        {
            require_once 'App/Controller/Action/Helper/Stage/Email.php';
            Zend_Controller_Action_HelperBroker::addHelper(new App_Controller_Action_Helper_Stage_Email());
        }
        else
        {
            Zend_Controller_Action_HelperBroker::addHelper(new App_Controller_Action_Helper_Email());
        }
	}

    /* Set up route to CMS controller
     *
     * @return void
     */
    protected function _initRouter()
    {
        if (PHP_SAPI == 'cli')
        {
            if (class_exists('Custom_Controller_Router_Cli'))
            {
                $this->bootstrap ('frontcontroller');
                $front = $this->getResource('frontcontroller');
                $front->setRouter(new Custom_Controller_Router_Cli());
                $front->setRequest(new Zend_Controller_Request_Simple());
                return $front;
            }
        }
    }
}

