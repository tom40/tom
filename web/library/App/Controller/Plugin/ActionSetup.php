<?php

class App_Controller_Plugin_ActionSetup extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if (!$request->isXmlHttpRequest() && $request->getModuleName() === 'default') {
            $front = Zend_Controller_Front::getInstance();
            if (!$front->hasPlugin('Zend_Controller_Plugin_ActionStack')) {
                $actionStack = new Zend_Controller_Plugin_ActionStack();
                $front->registerPlugin($actionStack, 97);
            } else {
                $actionStack = $front->getPlugin('Zend_Controller_Plugin_ActionStack');
            }

            // Menu
            // Hide menu if login action is requested
            if ($request->getActionName() != 'login') {
            	$action = clone($request);
            	$action->setActionName('menu')
                   ->setControllerName('nav');
            	$actionStack->pushStack($action);
            }


        }
    }
}