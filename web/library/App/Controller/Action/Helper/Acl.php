<?php

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @see App_Acl
 */
require_once 'App/Acl.php';


/**
 * ACL integration
 *
 * App_Controller_Action_Helper_Acl provides ACL support to a controller.
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @package    Controller
 * @subpackage Controller_Action
 * @copyright Copyright (c) 2012 Take Note
 */
class App_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var App_Controller_Plugin_Acl
     **/
    protected $_aclPlugin;

    /**
     * Constructor
     *
     * @param App_Controller_Plugin_Acl $aclPlugin Acl plugin object
     *
     * @return self
     **/
    public function __construct(App_Controller_Plugin_Acl $aclPlugin)
    {
        $this->_aclPlugin = $aclPlugin;
    }

    /**
     * Returns the Acl Plugin object
     *
     * @return App_Controller_Plugin_Acl
     **/
    public function getAclPlugin()
    {
        return $this->_aclPlugin;
    }

    /**
     * Call the denyAccess function of the Acl Plugin object
     *
     * @return void
     **/
    public function denyAccess()
    {
        $this->_aclPlugin->denyAccess();
    }
    
    public function isAccessAllowed($userId, $controller, $action)
    {
    	return $this->_aclPlugin->isAccessAllowed($userId, $controller, $action);
    }
    
}