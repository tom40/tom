<?php

class Application_Model_SupportFileDownloadMapper extends App_Db_Table
{
	protected $_name = 'support_files_downloads';
	protected $_shadowExists = false;
		
	public function makeFetchSelect($db, $jobId=null)
	{
		$select = $db->select();
		$select->from(array('sfd' => 'support_files_downloads'));
		$select->joinLeft(array('u' => 'users'), 'u.id = tfd.created_user_id', array('user' => 'name'));
		
		return $select;
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('sjd.id = ?', $id);
		
		$results = $db->fetchRow($select);
	
		return $results;
	}
}