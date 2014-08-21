<?php

/**
 *
 * @package    App
 * @subpackage App_Controller
 * @copyright  Copyright (c) 2012
 */
class App_Controller_Action extends Zend_Controller_Action
{

    /**
     * ID of the current user
     *
     * @var int
     */
    protected $_identity;

    /**
     * ACL plugin
     * @var App_Controller_Plugin_Acl
     */
    protected $_acl;

    /**
     * Flash messenger
     * @var object
     */
    protected $_flash;

    /**
     * Flash messenger
     * @param
     */

    /**
     * Init method
     *
     * @return void
     */
    public function init()
    {
        if (Zend_Auth::getInstance()->hasIdentity())
        {
            $acl             = $this->_helper->getHelper('acl');
            $this->_acl      = $acl->getAclPlugin();
        }
        else
        {
            if ($this->getRequest()->getActionName() != 'login' && !$this->getRequest()->isPost())
            {
                $this->_helper->redirector('login', 'Auth');
            }
        }
        $this->_flash = $this->_helper->getHelper('flashMessenger');

        if ( $this->_isAjax() )
        {
            $this->_helper->layout->disableLayout();
        }

        parent::init();
    }

    /**
     * Test if request is AJAX
     *
     * @return bool
     */
    protected function _isAjax()
    {
        return $this->getRequest()->isXmlHttpRequest();
    }

    /**
     * Deny action
     *
     * @return void
     */
    public function deniedAction()
    {
        $this->view->hasTopMenu = false;
        $this->render('denied');
    }

    /**
     * Pre-dispatch routines
     *
     * Called before action method. If using class with
     * {@link Zend_Controller_Front}, it may modify the
     * {@link $_request Request object} and reset its dispatched flag in order
     * to skip processing the current action.
     *
     * @return void
     */
    public function preDispatch()
    {
		// set default top menu display switch
    	$this->view->hasTopMenu = true;

    	if (Zend_Auth::getInstance()->hasIdentity())
        {
    		$this->_identity = Zend_Auth::getInstance()->getIdentity();
    		$this->view->hasIdentity = true;
    		$this->view->identity = Zend_Auth::getInstance()->getIdentity();
    		$this->view->username = Zend_Auth::getInstance()->getIdentity()->username;
    	}
        else
        {
    		$this->view->hasIdentity = false;
			if ($this->getRequest()->getActionName() != 'login' && !$this->getRequest()->isPost())
            {
	    		$this->_helper->redirector('login', 'Auth');
			}
    	}
    }
}