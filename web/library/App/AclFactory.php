<?php

class App_AclFactory
{

    /**
     * Returns an Acl object that is already setup
     *
     * @return App_Acl
     */
//    public function createAcl(Model_User $user)
    public function createAcl($userId, $aclGroupId, $aclRoleId)
    {
        $acl = new Zend_Acl();

        // add roles
        // we use $userId for the role to examine access to controllers and actions via the acl_groups table
        // and later we use $aclRoleId to examine access to specific objects (eg jobs or audio-jobs)
		$acl->addRole(new Zend_Acl_Role($userId));
		
        // add resources - these are the actions that users are allowed to perform. They are held
        // in acl_privileges
		$privilegeMapper = new Application_Model_AclGroupPrivilegeMapper();
		$actionResources = $privilegeMapper->fetchControllersByAclGroupId($aclGroupId);
		
		foreach ($actionResources as $resource) {
			$acl->add(new Zend_Acl_Resource($resource['controller']));
		}
        
		$actionResources = $privilegeMapper->fetchByAclGroupId($aclGroupId);

		foreach ($actionResources as $resource) {
			$acl->allow($userId, $resource['controller'], $resource['action']);
		}
		
		// NOW add specific objects
		// check if $userId and $aclRoleId are the same
		if (!$acl->hasRole($aclRoleId)) {
			$acl->addRole(new Zend_Acl_Role($aclRoleId));
		}
		
		$aclMapper = new Application_Model_AclMapper();
		
		$aclResults = $aclMapper->fetchResourceIdsByRoleId($aclRoleId);
// 		Zend_Debug::dump($aclResults);
		foreach ($aclResults as $result) {
// 			Zend_Debug::dump('Adding resource id : ' . $result['resource_id']);
			$acl->add(new Zend_Acl_Resource($result['resource_id']));
		}

		$aclResults = $aclMapper->fetchByRoleId($aclRoleId);
// 		Zend_Debug::dump($aclResults);
		foreach ($aclResults as $result) {
// 			Zend_Debug::dump('Adding $aclRoleId: ' . $aclRoleId . ' and resource id : ' . $result['resource_id'] . ' and privilege id ' . $result['privilege_id']);
			$acl->allow($aclRoleId, $result['resource_id'], $result['privilege_id']);
		}
		// Return the Acl
		return $acl;
		
        // Add the user and their group(s) as roles in the Acl
//         $this->_createRoles($acl, $user);
        
        // Add all module-controller-actions as resources into the Acl if required
		// Not required yet!
		
//         $acl->allow('guest', null, 'view');
//         $acl->allow('staff', null, 'update');
//         // Add all the current jobs as resources into the Acl
//         $model = new Model_Jobs();
//         $jobs = $model->fetchJobIdsByUserId($user->id);
//         foreach ($jobs as $jobId) {
//             $acl->addResource($jobId);
//         }

//         $acl->allow($roleId, $resourceId, $privilegeId);
        
//         // Fetch the acl role-resource-privileges from the database Acl table
//         $model = new Model_Acls();

//         // Ideally we'd just get the privileges for the current user and any
//         // groups they belong to, but it's probably quicker (less database
//         // connections) to just get all and iterate through the entire lot.

// //        $privileges = $model->fetchAllByRoleId($user->getRoleId());
//         $privileges = $model->fetchAllInstances();
//         $this->_setPrivileges($acl, $privileges);

//        // System admin users should be allowed access to everything
//        if ($user->isSystemAdmin()) {
//            $acl->allow();
//        }

        
    }

    /**
     *
     *
     * @param App_Acl $acl
     * @param array $privileges
     * @return void
     */
    private function _setPrivileges(App_Acl $acl, $privileges)
    {
        foreach ($privileges as $privilege) {

//            Zend_Debug::dump($privilege, '$privilege');
            $roleId      = $privilege['role_id'];
            $resourceId  = $privilege['resource_id'];
            $privilegeId = $privilege['privilege_id'];

            // The user's roles should already exist so we can ignore the ones that don't
            if (!$acl->hasRole($roleId)) {
//                Zend_Debug::dump($roleId, '$roleId');
                continue;
            }

            // Check the resource exists in the Acl
            if (!$acl->has($resourceId)) {
//                Zend_Debug::dump($resourceId, '$resourceId');
                continue;
            }

            if ($privilege['mode'] == Model_Acl::MODE_ALLOW) {
//                echo "<p>allow($roleId, $resourceId, $privilegeId)</p>";
                $acl->allow($roleId, $resourceId, $privilegeId);
            } elseif ($privilege['mode'] == Model_Acl::MODE_DENY) {
//                echo "<p>deny($roleId, $resourceId, $privilegeId)</p>";
                $acl->deny($roleId, $resourceId, $privilegeId);
            }
        }
    }

    /**
     *
     *
     * @param App_Acl $acl
     * @param User $user
     * @return void
     */
//    private function _createRoles(App_Acl $acl, Model_User $user)
    private function _createRoles(App_Acl $acl, $user)
    {
    	$acl->addRole(new Zend_Acl_Role('guest'));
    	$acl->addRole(new Zend_Acl_Role('staff'), 'guest');
    	
//         $guestRole = new Zend_Acl_Role(0);

// //        echo "<p>Create groups</p>";
//         if ($user instanceof Model_User) {

//             $groups = array_merge($user->getGroups(), array($guestRole));

//             // Add groups first so user can inherit them
//             foreach ($groups as $group) {
//     //            Zend_Debug::dump($group->getRoleId(), '$group->getRoleId()');
// //                echo '<p>$acl->addRole(' . $group->getRoleId() . ')';
//                 if (!$acl->hasRole($group)) {
//                     $acl->addRole($group);
//                 }
//             }

//     //        Zend_Debug::dump($user->getRoleId(), '$user->getRoleId()');
// //            echo '<p>$acl->addRole(' . $user->getRoleId() . ', ' . $user->getGroups() . ')';
// //            $acl->addRole($user, $user->getGroups());

// //            $parents = array_merge($user->getGroups(), array($guestRoleId));
//             $acl->addRole($user, $groups);
//         }


//         else {

//             if (!$acl->hasRole($guestRole)) {
//                 $acl->addRole($guestRole);
//             }

//             if (!$acl->hasRole($user)) {
//     //            $acl->addRole($user);
//                 $acl->addRole($user, $guestRole);
//             }

//         }

// //        $acl->addRole(new Zend_Acl_Role(2));
    }

}
