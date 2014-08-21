<?php

class Application_Model_PriorityMapper extends App_Db_Table
{
	protected $_name = 'lkp_priorities';
	
	public function makeFetchSelect($db, $current=1)
	{
		$select = $db->select();
	
		$select = $db->select();
		$select->from(array('lp' => 'lkp_priorities'));
		
		return $select;
	
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('lp.id = ?', $id);
	
		$results = $db->fetchRow($select);
	
		return $results;
	}
	
	public function fetchAllForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();
	
		$select->from(array('lpf' => 'lkp_priorities'), array('key' =>'id', 'value' => 'name', 'flag_colour'));
		$select->order('sort_order');
	
		$results = $db->fetchAll($select);
		return $results;
	}
}