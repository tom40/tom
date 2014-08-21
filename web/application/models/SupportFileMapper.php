<?php

class Application_Model_SupportFileMapper extends App_Db_Table
{
	protected $_name = 'support_files';
	
	public function makeFetchSelect($db, $jobId=null)
	{
		$select = $db->select();
		
		$select->from(array('sf' => 'support_files'));
		$select->joinLeft(array('u' => 'users'), 'u.id = sf.created_user_id', array('user' => 'name'));
		
		return $select;
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('sf.id = ?', $id);
		
		$results = $db->fetchRow($select);
	
		return $results;
	}
	
	public function fetchCurrentByJobId($jobId)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('sf.job_id = ?', $jobId);
		$select->where('archived = 0');
	
		$results = $db->fetchAll($select);

		return $results;
	}
	
	public function getUploadKeyCount($uploadKey)
	{
		$db = $this->getAdapter();
		$select = $db->select();
	
		$select->from(array('sf' => 'support_files'), array('COUNT(id)'));
		$select->where('upload_key = ?', $uploadKey);
	
		$results = $db->fetchOne($select);
		return $results;
	}
	
	public function fetchByUploadKey($uploadKey, $sortField='sf.id')
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('sf.upload_key = ?', $uploadKey);
		$select->order($sortField);
	
		$results = $db->fetchAll($select);
		return $results;
	}
}