<?php

class Application_Model_ClientsUserMapper extends App_Db_Table
{
	protected $_name = 'clients_users';

	public function makeFetchSelect($db, $current=1)
	{
		$select = $db->select();

		$select->from(array('cu' => 'clients_users'));

		return $select;

	}

	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('cu.id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}

	public function fetchByUserId($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('cu.user_id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}

    public function fetchFullDataByUserId($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
        $select->joinInner(array('u' => 'users'), 'u.id = cu.user_id')
               ->where('cu.user_id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}

    public function fetchByClientId($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
        $select->joinInner(array('u' => 'users'), 'u.id = cu.user_id')
               ->where('cu.client_id = ?', $id);

		$results = $db->fetchAll($select);

		return $results;
	}

    public function fetchClientUserColleagues($userId, $clientId)
    {
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
        $select->joinInner(array('u' => 'users'), 'cu.user_id = u.id', array('name', 'acl_role_id'));

		$select->where('cu.client_id = ?', $clientId)
               ->where('cu.user_id != ?', $userId);

		$results = $db->fetchAll($select);
		return $results;
    }

	public function fetchByClientIdForDropdown($clientId)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		// staff should never see client names
		if ($this->_getCurrentUserAclGroupId() == 2) {
			return;
		}

		$select->from(array('cu' => 'clients_users'), array('key' =>'user_id'));
		$select->join(array('u' => 'users'), 'cu.user_id = u.id', array('value' => 'name'));
		$select->where('cu.client_id = ?', $clientId);

		// client users only see their own name
		if ($this->_getCurrentUserAclGroupId() == 3) {
			$select->where('u.id = ?', $this->_getCurrentUserId());
		}
		$select->order('name');

		$results = $db->fetchAll($select);
		return $results;
	}
}

