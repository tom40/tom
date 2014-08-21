<?php

/**
 * @see Zend_Acl
 */
require_once 'Zend/Acl.php';


class App_Acl extends Zend_Acl
{

    /**
     * Stores the module/controller/action to be used if user not authenticated
     *
     * @var array
     */
    private $_noAuth;

    /**
     * Stores the module/controller/action to be used if user not authorised
     *
     * @var array
     */
    private $_noAcl;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
//        $config = Zend_Registry::get('config');
//        $roles = $config->acl->roles;
//        $this->_addRoles($roles);

        // Load 'Guest', from which all user account must inherent
//        $roleId = 0;
//        $this->addRole(new Zend_Acl_Role($roleId));

        $this->_loadRedirectionActions();
    }

    /**
     * Get the module/controller/action to be used if user not authenticated
     *
     * @return array
     */
    public function getNoAuthAction()
    {
        return $this->_noAuth;
    }

    /**
     * Get the module/controller/action to be used if user not authorised
     *
     * @return array
     */
    public function getNoAclAction()
    {
        return $this->_noAcl;
    }

    /**
     * Set up the redirect (not authenticated / not authorised) actions
     *
     * @return void
     */
    protected function _loadRedirectionActions()
    {
        $this->_noAuth = array(
            'module'     => 'default',
            'controller' => 'auth',
            'action'     => 'login'
        );

        $this->_noAcl = array(
            'module'     => 'default',
            'controller' => 'auth',
            'action'     => 'privilege'
        );
    }

    /**
     * Returns true if and only if the Role has access to the Resource
     *
     * The $role and $resource parameters may be references to, or the string identifiers for,
     * an existing Resource and Role combination.
     *
     * If either $role or $resource is null, then the query applies to all Roles or all Resources,
     * respectively. Both may be null to query whether the ACL has a "blacklist" rule
     * (allow everything to all). By default, Zend_Acl creates a "whitelist" rule (deny
     * everything to all), and this method would return false unless this default has
     * been overridden (i.e., by executing $acl->allow()).
     *
     * If a $privilege is not provided, then this method returns false if and only if the
     * Role is denied access to at least one privilege upon the Resource. In other words, this
     * method returns true if and only if the Role is allowed all privileges on the Resource.
     *
     * This method checks Role inheritance using a depth-first traversal of the Role registry.
     * The highest priority parent (i.e., the parent most recently added) is checked first,
     * and its respective parents are checked similarly before the lower-priority parents of
     * the Role are checked.
     *
     * @param  Zend_Acl_Role_Interface|string     $role
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @param  string                             $privilege
     * @uses   Zend_Acl::get()
     * @uses   Zend_Acl_Role_Registry::get()
     * @return boolean
     *
     * (non-PHPdoc)
     * @see library/Zend/Zend_Acl#isAllowed($role, $resource, $privilege)
     */
    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        if ($role instanceof Model_User || $role instanceof Model_Group) {
            if ($role->isSystemAdmin()) {
                return true;
            }
        }

        $result = parent::isAllowed($role, $resource, $privilege);
        return $result;
    }

    /**
     * Determine whether the role is a system admin
     *
     * @param Zend_Acl_Role_Interface|int $role
     * @return string|string
     */
    public function isSystemAdmin($role = null)
    {
        if (empty($role)) {
            return false;
        }

        if ($role instanceof Model_User || $role instanceof Model_Group) {
            if ($role->isSystemAdmin()) {
                return true;
            }
            return false;
        }

        // Check if group
        require_once 'Groups.php';
        $modelGroups = new Model_Groups();
        $group = $modelGroups->fetchByAclRoleId($role);
        if ($group) {
            $group = $modelGroups->fetchInstance($group['id']);
            return $group->isSystemAdmin();
        }

        // Check if user
        require_once 'Users.php';
        $modelUsers = new Model_Users();
        $user = $modelUsers->fetchByAclRoleId($role);
        if ($user) {
            $user = $modelUsers->fetchInstance($user['id']);
            return $user->isSystemAdmin();
        }

        return false;
    }

}
