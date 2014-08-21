<?php

class Application_Model_AudioFileQualityMapper extends App_Db_Table
{
	protected $_name = 'lkp_audio_file_quality';
	
	public function makeFetchSelect($db, $current=1)
	{
		$select = $db->select();
	
		$select = $db->select();
		$select->from(array('lafq' => 'lkp_audio_file_quality'));
		
		return $select;
	
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('lafq.id = ?', $id);
	
		$results = $db->fetchRow($select);
	
		return $results;
	}
	
	public function fetchAllForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();
	
		$select->from(array('lafq' => 'lkp_audio_file_quality'), array('key' =>'id', 'value' => 'name', 'warning' => 'warning'));
		$select->order('sort_order');
	
		$results = $db->fetchAll($select);
		return $results;
	}
}

