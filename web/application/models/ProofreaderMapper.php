<?php

class Application_Model_ProofreaderMapper extends App_Db_Table
{
	protected $_name = 'proofreaders';

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_Proofreader_Row';

	public function makeFetchSelect($db)
	{
		$select = $db->select();
	
		$select->from(array('p' => 'proofreaders'));
		$select->joinLeft(array('u' => 'users'), 'p.user_id = u.id', array('user_id' => 'id', 'user_name' => 'name'));
		$select->joinLeft(array('lpg' => 'lkp_proofreader_grades'), 'p.grade_id = lpg.id', array('proofreader_grade' => 'name'));
		
		return $select;
	
	}
	
	public function fetchCurrent()
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
		$select->order('user_name');
		$results = $db->fetchAll($select);
	
		return $results;
	}
	
	public function fetchOnOffShift($audioJobId)
	{
		$db = $this->getAdapter();
	
		// build a temp table with the user_ids of any typists assigned to this audio job
		$sql = 'CREATE TEMPORARY TABLE tmp_audio_proofreaders (`user_id` int(11), KEY `ix_user_id` (`user_id`)) ENGINE=InnoDB CHARACTER SET=utf8';
		$db->query($sql);
	
		$select = $db->select();
		$select->from(array('ajt' => 'audio_jobs_proofreaders'), array('user_id'));
		$select->where('audio_job_id = ?' , $audioJobId);
		$select->where('current = ?' , 1);
		$select->group('user_id');
	
		$sql = 'INSERT INTO tmp_audio_proofreaders (user_id) ' . $select->__toString();
		$db->query($sql);
	
		$select = $this->makeFetchSelect($db);
	
		$select->join(array('us' => 'users_shifts'), 'u.id = us.user_id', array());
		$select->where('shift_date = ?', date('Y-m-d', time()));
	
		$today = getdate();
		if ($today['hours'] < 12) {
			$select->where('shift_id = ?', 1);
		} else {
			$select->where('shift_id = ?', 2);
		}
		$results = array();
		$results['onShift'] = $db->fetchAll($select);
	
		$sql = 'CREATE TEMPORARY TABLE tmp_proofreaders (`user_id` int(11), KEY `ix_user_id` (`user_id`)) ENGINE=InnoDB CHARACTER SET=utf8';
		$db->query($sql);
		$select->reset( Zend_Db_Select::COLUMNS );
		$select->reset( Zend_Db_Select::ORDER );
		$select->columns('user_id');
	
		$sql = 'INSERT INTO tmp_proofreaders (user_id) ' . $select->__toString();
	
		$db->query($sql);
		$select = $this->makeFetchSelect($db);
	
		// exclude typists on shift
		$select->joinLeft(array('tp' => 'tmp_proofreaders'), 'u.id = tp.user_id', array());
		$select->where('tp.user_id is null');
	
		$results['offShift'] = $db->fetchAll($select);
	
		$sql = 'DROP TEMPORARY TABLE IF EXISTS tmp_proofreaders';
		$db->query($sql);
		
		$sql = 'DROP TEMPORARY TABLE IF EXISTS tmp_audio_proofreaders';
		$db->query($sql);

		return $results;
	}
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('p.id = ?', $id);
	
		$results = $db->fetchRow($select);
	
		return $results;
	}

	public function fetchByUserId($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
	
		$select->where('p.user_id = ?', $id);
	
		$results = $db->fetchRow($select);
	
		return $results;
	}
	
	public function fetchAllGradesForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();
	
		$select->from(array('ltg' => 'lkp_proofreader_grades'), array('key' =>'id', 'value' => 'name'));
		$select->order('sort_order');
	
		$results = $db->fetchAll($select);
		return $results;
	}
}