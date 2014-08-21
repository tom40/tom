<?php

class Application_Model_UserMapper extends App_Db_Table
{
    const CLIENT_ACL_GROUP_ID             = 3;
    const COMPLETED_REQUIRES_PR_STATUS_ID = 7;
    const RETURNED_TO_CLIENT_STATUS_ID    = 17;
    const STAFF_ACL_GROUP_ID              = 2;

	protected $_name = 'users';

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_User_Row';

	public function makeFetchSelect($db, $current=1)
	{
		$select = $db->select();

		$select->from(array('u' => 'users'));
		$select->join(array('ag' => 'acl_groups'), 'ag.id = u.acl_group_id', array('acl_group' => 'name'));
		$select->joinLeft(array('cu' => 'clients_users'), 'cu.user_id = u.id', array());
		$select->joinLeft(array('c' => 'clients'), 'c.id = cu.client_id', array('client' => 'name'));

		return $select;

	}

    public function getAllTypistsAndProofreaders()
	{
        $db     = $this->getAdapter();
		$select = $db->select();
		$select->from(array('u' => 'users'), array('key' => 'id', 'value' => 'name'));
        $select->where('u.acl_group_id = ?', self::STAFF_ACL_GROUP_ID)
            ->where('u.active = ?', '1')
            ->order('value ASC');
        
		$results = $db->fetchAll($select);
		return $results;

	}

	public function fetchAll()
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$results = $db->fetchAll($select);
		return $results;
	}

	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('u.id = ?', $id);

		$results = $db->fetchRow($select);
		return $results;
	}

	public function fetchAllForDropdown($aclGroupId)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('u' => 'users'), array('key' =>'id', 'value' => 'name'));
		$select->order('name');

		if (!is_null($aclGroupId)) {
			$select->where('u.acl_group_id = ?', $aclGroupId);
		}

		$results = $db->fetchAll($select);
		return $results;
	}

    public function fetchByEmailList($emailList)
    {
        $db = $this->getAdapter();
        $select = $db->select();
		$select->from(array('u' => 'users'), array('name', 'email'));
        $select->where("email IN ($emailList)");
        $results = $db->fetchAll($select);
        return $results;
    }
}

