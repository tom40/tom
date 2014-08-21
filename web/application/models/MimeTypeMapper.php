<?php

class Application_Model_MimeTypeMapper extends App_Db_Table
{
	protected $_name = 'lkp_mime_types';
	
	public function makeFetchSelect($db, $current=1)
	{
		$select = $db->select();
	
		$select = $db->select();
		$select->from(array('lmt' => 'lkp_mime_types'));
		
		return $select;
	
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('lmt.id = ?', $id);
	
		$results = $db->fetchRow($select);
	
		return $results;
	}
	
	public function fetchAllForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();
	
		$select->from(array('lmt' => 'lkp_mime_types'), array('key' =>'name', 'value' => 'description'));
		$select->order('name');
	
		$results = $db->fetchAll($select);
		return $results;
	}
}

