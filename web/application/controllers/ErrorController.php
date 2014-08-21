<?php

class ErrorController extends App_Controller_Action
{

	public function deniedAction() 
	{
		$this->view->hasTopMenu = false;
	}
	
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }
        
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $this->_logErrors($log, $this->view->message, $errors);
        }

        $config = Zend_Registry::get('config');

        if (($config->app->errors->sentry)) {
            require(APPLICATION_PATH . '/../library/Raven/Autoloader.php');
            Raven_Autoloader::register();

            $client = new Raven_Client($config->app->errors->sentry);
            $client->captureException($errors->exception);
        }


        $this->view->exception = $errors->exception;
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    /**
     * Logs the error(s).
     *
     * @param Zend_Log    $log
     * @param string      $message
     * @param ArrayObject $errors
     */
    protected function _logErrors($log, $message, $errors)
    {
        // Ignore Bit.ly bot
        if ($_SERVER['HTTP_USER_AGENT'] == 'bitlybot') {
            return;
        }

        // Implode params into a string
        $params = '';
        foreach ($errors->request->getParams() as $key => $param) {
            $params .= $key . ' => ' . $param . "\n";
        }

        // Goes to both writers
        $msg = "\n-- An Error Occurred --\n"
            . $message ."\n"
            . $errors->exception->getMessage() . "\n"
            . "\n-- Exception Details --\n"
            . $errors->exception->getTraceAsString() . "\n"
            . "\n-- Method --\n"
            . $errors->request->getMethod() . "\n"
            . "\n-- Uri --\n"
            . $errors->request->getRequestUri() . "\n"
            . "\n-- Request Parameters --\n"
            . $params
            . "\n-- User Agent --\n"
            . $_SERVER['HTTP_USER_AGENT'] . "\n"
            . "\n-- HTTP Response Code --\n"
            . $this->getResponse()->getHttpResponseCode() . "\n"
            . "--------------------------------------------------------------------------------";

        $log->crit($msg);
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

