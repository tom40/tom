<?php

class Application_Model_JobChangeRequestMapper extends App_Db_Table
{
	protected $_name = 'job_change_requests';
	protected $_shadowExists = false;

	public function makeFetchSelect($db)
	{
		$select = $db->select();
	
		$select->from(array('jcr' => 'job_change_requests'), array('*', 'created_date_unix' => 'UNIX_TIMESTAMP(jcr.created_date)'));
		$select->join(array('u' 	=> 'users'), 'jcr.created_user_id = u.id', array('created_user_name' => 'name'));
		$select->joinLeft(array('j' => 'jobs'), 'jcr.job_id = j.id', array('job_title' => 'title'));
		
		return $select;
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('jcr.id = ?', $id);
		$results = $db->fetchRow($select);
		return $results;
	}
}