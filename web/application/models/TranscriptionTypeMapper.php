<?php

class Application_Model_TranscriptionTypeMapper extends App_Db_Table
{
	protected $_name = 'lkp_transcription_types';

	public function makeFetchSelect($db, $current=1)
	{
		$select = $db->select();

		$select = $db->select();
		$select->from(array('ltt' => 'lkp_transcription_types'));

		return $select;

	}

	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ltt.id = ?', $id)
            ->joinLeft(array('tt' => 'lkp_turnaround_times'), 'tt.id = ltt.turnaround_id', array('turnaround_time' => 'name'));

		$results = $db->fetchRow($select);

		return $results;
	}

    /**
     * Can delete
     *
     * @param int $transcriptionTypeId Transcription Type ID
     *
     * @return bool
     */
    public function canDelete($transcriptionTypeId)
    {
        $jobMapper      = new Application_Model_JobMapper();
        $job            = $jobMapper->fetchByTranscriptionType($transcriptionTypeId);
        $audioJobMapper = new Application_Model_AudioJobMapper;
        $audioJob       = $audioJobMapper->fetchByTranscriptionType($transcriptionTypeId);
        if (!empty($job) || !empty($audioJob))
        {
            return false;
        }

        return true;
    }

	public function fetchAllForDropdown($clientId = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(array('ltt' => 'lkp_transcription_types'), array('key' =>'id', 'value' => 'name'));
        if (!empty($clientId))
        {
            $select->where('ltt.client_id = ?', $clientId);
        }
		$select->order('sort_order');

		$results = $db->fetchAll($select);

        if (empty($results))
        {
            $select = $db->select();
            $select->from(array('ltt' => 'lkp_transcription_types'), array('key' =>'id', 'value' => 'name'));
            $select->where('ltt.client_id IS NULL');
            $select->order('sort_order');
            $results = $db->fetchAll($select);
        }

		return $results;
	}

    /**
     * Fetch all transcription types with the turnaound time details attached
     *
     * @return array
     */
    public function fetchAll()
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('ltt' => 'lkp_transcription_types'));
        $select->joinLeft(array('tt' => 'lkp_turnaround_times'), 'tt.id = ltt.turnaround_id', array('turnaround_time' => 'name'));
		$select->order('ltt.sort_order');

		$results = $db->fetchAll($select);
		return $results;
    }
}

