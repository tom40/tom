<?php

/** App_Acl */
require_once 'App/Acl.php';

/** Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';


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
class App_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{

    const ACL_GROUP_SUPERADMIN = 4;
    const ACL_GROUP_ADMIN      = 1;
    const ACL_GROUP_CLIENT     = 3;


    /**
     * Admin group types
     * @var array
     */
    protected $_adminGroups = array(
        self::ACL_GROUP_ADMIN,
        self::ACL_GROUP_SUPERADMIN
    );

    /**
     * @var Zend_Auth
     */
    protected $_auth;

    /**
     * @var App_Acl
     */
    protected $_acl;

    /**
    * @var int
    */
    protected $_aclGroupId;
    
    /**
    * @var array
    */
    protected $_errorPage;
    
    /**
     * Constructor
     *
     * Optionally set view object and options.
     *
     * @param  Zend_Acl $acl ACL object
     *
     * @return self
     */
    public function __construct(Zend_Acl $acl)
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_acl = $acl;
        
        $this->_errorPage = array(
			'module' 		=> 'default',
            'controller' 	=> 'error', 
            'action' 		=> 'denied'
        );
        
    }

    public function getAcl()
    {
    	return $this->_acl;
    }
    
    /**
    * Sets the error page
    *
    * @param string $action
    * @param string $controller
    * @param string $module
    *
    * @return void
    **/
    public function setErrorPage($action, $controller = 'error', $module = null)
    {
    	$this->_errorPage = array(
    		'module'     => $module,
            'controller' => $controller,
            'action'     => $action
    	);
    }
    
    /**
     * Returns the error page
     *
     * @return array
     **/
    public function getErrorPage()
    {
    	return $this->_errorPage;
    }
    
    /**
     * Hook into action controller preDispatch() workflow
     *
     * @param Zend_Controller_Request_Abstract $request Request object
     *
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

		if (!$this->_auth->hasIdentity())
        {
			$this->_request->setModuleName('default');
			$this->_request->setControllerName('auth');
			$this->_request->setActionName('login');
			return;
		}
		$userId = (int)$this->_auth->getIdentity()->id;
		$this->_aclGroupId = (int)$this->_auth->getIdentity()->acl_group_id;

        $controller = $request->getControllerName();
        $action     = $request->getActionName();

        if ($this->_acl->hasRole($userId) && $this->_acl->has($controller) && $this->_acl->isAllowed($userId, $controller, $action))
        {
			// do nothing - allow user to proceed
        }
        else
        {
        	// deny access unless user requesting logout action or is admin
        	if ($this->isAdmin())
            {
        			// admin
        		// allow user to proceed
        	}
            elseif ($controller == 'auth' && $action == 'logout')
            { // logout action
        		// allow user to proceed
        	}
            else
            {
        		$this->denyAccess();
        	}
        }
    }

    /**
     * Deny all but super admin users
     *
     * @return void
     */
    public function restrictToSuperAdmin()
    {
        if (!$this->isSuperAdmin())
        {
            $this->_request->setDispatched(false);
            $this->denyAccess();
        }
    }

    /**
     * Deny all but admin and super admin users
     *
     * @return void
     */
    public function restrictToAdmin()
    {
        if ( !$this->isAdmin() && !$this->isSuperAdmin() )
        {
            $this->_request->setDispatched( false );
            $this->denyAccess();
        }
    }

    /**
    * Deny Access Function
    * Redirects to errorPage, this can be called from an action using the action helper
    *
    * @return void
    **/
    public function denyAccess()
    {
        return true;
    	$this->_request->setModuleName($this->_errorPage['module']);
    	$this->_request->setControllerName($this->_errorPage['controller']);
    	$this->_request->setActionName($this->_errorPage['action']);
    }

    public function isAccessAllowed($role, $resource, $privilege)
    {
    	if ($this->isAdmin())
        {	// admin
    		return true;
    	}

    	if ($this->_acl->hasRole($role) && $this->_acl->has($resource) && $this->_acl->isAllowed($role, $resource, $privilege))
        {
    		return true;
    	}
        else
        {
    		return false;
    	}
    }

    /**
     * Is an admin group
     *
     * @param int|null $aclGroupId Group ID to test.  If null use current user
     *
     * @return bool
     */
    public function isAdmin($aclGroupId = null)
    {
        if (null === $aclGroupId)
        {
            $aclGroupId = $this->_aclGroupId;
        }
        return (in_array($aclGroupId, $this->_adminGroups));
    }

    /**
     * Is super admin
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return ($this->_aclGroupId == self::ACL_GROUP_SUPERADMIN);
    }

    /**
     * Is an admin group
     *
     * @param int|null $aclGroupId Group ID to test.  If null use current user
     *
     * @return bool
     */
    public function isClient($aclGroupId = null)
    {
        if (null === $aclGroupId)
        {
            $aclGroupId = $this->_aclGroupId;
        }
        return ( $aclGroupId == self::ACL_GROUP_CLIENT );
    }
}
