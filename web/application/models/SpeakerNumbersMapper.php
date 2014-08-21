<?php

class Application_Model_SpeakerNumbersMapper extends App_Db_Table
{
	protected $_name = 'lkp_speaker_numbers';
	
	public function makeFetchSelect($db, $current=1)
	{
		$select = $db->select();
	
		$select = $db->select();
		$select->from(array('lsn' => 'lkp_speaker_numbers'));
		
		return $select;
	
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('lsn.id = ?', $id);
	
		$results = $db->fetchRow($select);
	
		return $results;
	}
	
	public function fetchAllForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();
	
		$select->from(array('lsn' => 'lkp_speaker_numbers'), array('key' =>'id', 'value' => 'name'));
		$select->order('sort_order');
	
		$results = $db->fetchAll($select);
		return $results;
	}
}

