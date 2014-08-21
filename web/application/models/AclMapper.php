<?php

class Application_Model_AclMapper extends App_Db_Table
{
	protected $_name = 'acl';
	protected $_shadowExists = false;

	public function fetchByRoleId($roleId)
	{
		$db = $this->getAdapter();

		$select = $db->select();
		$select->from(array('a' => 'acl'));
		$select->where('a.role_id = ?', $roleId);

		$results = $db->fetchAll($select);

		return $results;
	}

	public function fetchResourceIdsByRoleId($roleId)
	{
		$db = $this->getAdapter();

		$select = $db->select();
		$select->from(array('a' => 'acl'), array('resource_id'));
		$select->where('a.role_id = ?', $roleId);
		$select->group('a.resource_id');

		$results = $db->fetchAll($select);

		return $results;
	}

    public function hasResourceAccess($roleId, $resourceId, $privilegeId)
    {
		$db     = $this->getAdapter();
		$select = $db->select();
		$select->from(array('a' => 'acl'));
		$select->where('a.role_id = ?', $roleId);
        $select->where('a.resource_id = ?', $resourceId);
		$select->where('a.privilege_id = ?', $privilegeId);
        $select->where("a.mode = 'allow'");
		$result = $db->fetchAll($select);

        if (!empty($result))
        {
            return true;
        }

		return false;
    }

    /**
     * Remove user transcript access
     *
     * @param int $roleId               the user role id
     * @param int $jobResourceId        the job resource id
     * @param int $audioFileResourceId  the audio file resource id
     */
    public function removeTranscriptAccess($roleId, $jobResourceId, $audioFileResourceId)
    {
        $this->delete("role_id = '{$roleId}' AND resource_id = '{$audioFileResourceId}'");

        // Check if user has access to any other audio files in this project
        $db = $this->getAdapter();
		$select = $db->select();
		$select->from(array('aj' => 'audio_jobs'), array('id'))
               ->joinInner(array('j' => 'jobs'), 'j.id = aj.job_id', array())
               ->where('j.acl_resource_id = ?', $jobResourceId)
               ->group('aj.id');

		// if not an admin then restrict access only to authorised users
		if (!self::$_acl->isAdmin()) {
			$select->join(array('acl' => 'acl'), 'aj.acl_resource_id = acl.resource_id ' .
						"AND acl.role_id = '{$roleId}'" . // current users acl_role_id
						'AND acl.mode = \'allow\''
			, array());
		}

		$audioJobs = $db->fetchAll($select);

        // Remove access to the project if user has no other audio jobs in this project
        if (empty($audioJobs))
        {
            $this->delete("role_id = '{$roleId}' AND resource_id = '{$jobResourceId}'");
        }
    }

    /**
     * Grant access to a job
     *
     * @param type $userAclRoleId
     * @param type $jobAclResourceId
     */
    public function shareJobAccess($userAclRoleId, $jobAclResourceId)
    {
        $jobAccessPrivileges = array(1, 10, 11, 22, 38, 41, 42, 9, 50, 61);
        foreach ($jobAccessPrivileges as $privilegeId)
        {
            if (!$this->aclExists($userAclRoleId, $jobAclResourceId, $privilegeId, 'allow'))
            {
                $this->insert(array('role_id' => $userAclRoleId, 'resource_id' => $jobAclResourceId, 'privilege_id' => $privilegeId, 'mode' => 'allow'));
            }
        }
    }

    /**
     * Fetches other client users with access to project
     * This is necessary when a project is changed from one client to another
     * to ensure the previous client has no more access to this project
     *
     * @param int $resourceId
     * @param int $clientId
     *
     * @return void
     */
    public function fetchOtherClientUsersWithAccess($resourceId, $clientId)
    {
        $db = $this->getAdapter();
        $sql = "SELECT u.id as user_id, u.acl_role_id FROM users u
                INNER JOIN acl c ON c.role_id = u.acl_role_id
                INNER JOIN clients_users cu ON cu.user_id = u.id
                WHERE c.resource_id = {$resourceId} AND cu.client_id != {$clientId} GROUP BY u.id;";
        $results = $db->fetchAll($sql);
        return $results;
    }

    /**
     * Grant access to an audio job
     *
     * @param type $userAclRoleId
     * @param type $audioJobAclResourceId
     */
    public function shareAudioJobAccess($userAclRoleId, $audioJobAclResourceId)
    {
        $audioJobAccessPrivileges = array(5, 26, 18, 49, 29, 32);
        foreach ($audioJobAccessPrivileges as $privilegeId)
        {
            if (!$this->aclExists($userAclRoleId, $audioJobAclResourceId, $privilegeId, 'allow'))
            {
                $this->insert(array('role_id' => $userAclRoleId, 'resource_id' => $audioJobAclResourceId, 'privilege_id' => $privilegeId, 'mode' => 'allow'));
            }
        }
    }

    /**
     * Remove user job job access
     *
     * @param int $roleId      the user role id
     * @param int $resourceId  the resource id
     */
    public function removeJobAccess($roleId, $resourceId)
    {
        // Remove user access from a job
        if ($this->hasResourceAccess($roleId, $resourceId, 10))
        {
            $this->delete("role_id = '{$roleId}' AND resource_id = '{$resourceId}'");
        }
    }

    /**
     * Remove user audio job access
     *
     * @param int $userAclRoleId      the user role id
     * @param int $audioJobAclResourceId the resource id
     */
    public function removeAudioJobAccess($userAclRoleId, $audioJobAclResourceId)
    {
        // Remove user access from a job
        $audioJobAccessPrivileges = array(5, 26, 18, 49, 29, 32);
        foreach ($audioJobAccessPrivileges as $privilegeId)
        {
            if ($this->aclExists($userAclRoleId, $audioJobAclResourceId, $privilegeId, 'allow'))
            {
                $this->delete("role_id = '{$userAclRoleId}' AND resource_id = '{$audioJobAclResourceId}'");
            }
        }
    }

    /**
     * Check if ACL record exists
     *
     * @param int $roleId
     * @param int $resourceId
     * @param int $privilegeId
     * @param string $mode
     *
     * @return bool
     */
    public function aclExists($roleId, $resourceId, $privilegeId, $mode = 'allow')
    {
        $db = $this->getAdapter();
		$select = $db->select();
        $select->from(array('a' => 'acl'), array('CONCAT(resource_id)'));
		$select->where('a.role_id = ?', $roleId);
        $select->where('a.resource_id = ?', $resourceId);
        $select->where('a.privilege_id = ?', $privilegeId);
        $select->where('a.mode = ?',  $mode);
		$results = $db->fetchAll($select);
        if (!empty($results))
        {
            return true;
        }

        return false;
    }

    public function fetchResourceIdsList($roleId)
	{
		$db = $this->getAdapter();
		$select = $db->select();
        $select->from(array('a' => 'acl'), array('CONCAT(resource_id)'));
		$select->where('a.role_id = ?', $roleId);
		$select->group('a.resource_id');
		$results = $db->fetchCol($select);
		return $results;
	}

	public function fetchByResourceIdRoleIdAndPrivilege($resourceId, $roleId, $privilegeId)
	{
		$db = $this->getAdapter();

		$select = $db->select();
		$select->from(array('a' => 'acl'), array('id'));
		$select->where('a.resource_id = ?', $resourceId);
		$select->where('a.role_id = ?', $roleId);
		$select->where('a.privilege_id = ?', $privilegeId);

		$results = $db->fetchAll($select);

		return $results;
	}

}

