<?php

class Application_Model_AclGroupMapper extends App_Db_Table
{
	protected $_name = 'acl_groups';
	
	public function makeFetchSelect($db, $current=1)
	{
		$select = $db->select();
	
		$select = $db->select();
		$select->from(array('ag' => 'acl_groups'));
		
		return $select;
	
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('ag.id = ?', $id);
	
		$results = $db->fetchRow($select);
	
		return $results;
	}
	
	public function fetchAllForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();
	
		$select->from(array('ag' => 'acl_groups'), array('key' =>'id', 'value' => 'name'));
		$select->order('id');

        if (!self::$_acl->isSuperAdmin())
        {
            $select->where('id != ?', 4);
        }
	
		$results = $db->fetchAll($select);
		return $results;
	}
}

