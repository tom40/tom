<?php

class Application_Model_TranscriptionFileMapper extends App_Db_Table
{
	protected $_name = 'transcription_files';

	public function makeFetchSelect($db, $jobId=null)
	{
		$select = $db->select();

		$select->from(array('tf' => 'transcription_files'), array('*', 'created_date_unix' => 'UNIX_TIMESTAMP(tf.created_date)'));
		$select->join(array('aj' => 'audio_jobs'), 'aj.id = tf.audio_job_id', array('job_id', 'audio_job_file_name' => 'file_name'));
		$select->joinLeft(array('u' => 'users'), 'u.id = tf.created_user_id', array('user' => 'name', 'user_id' => 'id'));

		return $select;
	}

	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('tf.id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}

	public function fetchByAudioJobId($audioJobId, $showLatestRecordOnly = false)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('tf.audio_job_id = ?', $audioJobId);
		$select->where('tf.archived = 0');

        if ($showLatestRecordOnly)
        {
            $select->order('tf.created_date DESC');
            $select->limit(1);
        }

		$results = $db->fetchAll($select);

		return $results;
	}

	public function fetchLatestByAudioJobId($audioJobId)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('tf.audio_job_id = ?', $audioJobId);
        $select->where('tf.archived = 0');
		$select->order('id DESC');
		$select->limit(1);

		$results = $db->fetchRow($select);
		return $results;
	}

}