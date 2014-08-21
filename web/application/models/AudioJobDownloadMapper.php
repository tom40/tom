<?php

class Application_Model_AudioJobDownloadMapper extends App_Db_Table
{
	protected $_name = 'audio_jobs_downloads';
	protected $_shadowExists = false;
	
	public function makeFetchSelect($db)
	{
		$select = $db->select();
		$select->from(array('ajd' => 'audio_jobs_downloads'), array('download_date' => 'UNIX_TIMESTAMP(ajd.created_date)'));
		$select->join(array('aj' => 'audio_jobs'), 'aj.id = ajd.audio_job_id', array());
		$select->joinLeft(array('u' => 'users'), 'u.id = ajd.created_user_id', array('user' => 'name'));
		
		return $select;
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('ajd.id = ?', $id);
		
		$results = $db->fetchRow($select);
	
		return $results;
	}
	
	public function fetchByAudioJobId($audioJobId)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('ajd.audio_job_id = ?', $audioJobId);
		$select->order('ajd.created_date DESC');
		$results = $db->fetchAll($select);

		return $results;
	}
}