<?php

class Application_Model_AclGroupPrivilegeMapper extends App_Db_Table
{
	protected $_name = 'acl_group_privileges';
	
	public function makeFetchSelect($db)
	{
		$select = $db->select();
		$select->from(array('agp' => 'acl_group_privileges'));
		$select->join(array('ap' => 'acl_privileges'), 'ap.id = agp.privilege_id', array('controller', 'action'));
		return $select;
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('agp.id = ?', $id);
		
		$results = $db->fetchRow($select);
	
		return $results;
	}
	
	public function fetchByAclGroupIdAndObject($aclGroupId, $object)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('agp.group_id = ?', $aclGroupId);
		$select->where('ap.object = ?', $object);
		
		$results = $db->fetchAll($select);
	
		return $results;
	}
	
	public function fetchByAclGroupId($aclGroupId)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('agp.group_id = ?', $aclGroupId);
	
		$results = $db->fetchAll($select);
	
		return $results;
	}

	public function fetchControllersByAclGroupId($aclGroupId)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('agp.group_id = ?', $aclGroupId);
		$select->group('controller');
	
		$results = $db->fetchAll($select);
	
		return $results;
	}
}

