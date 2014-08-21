<?php

class Application_Model_AudioJobStatusMapper extends App_Db_Table
{

    const STATUS_APPROVED           = 2;
    const STATUS_UNASSIGNED         = 3;
    const STATUS_PART_ASSIGNED      = 4;
    const STATUS_COMPLETED_NEEDS_PR = 7;
    const STATUS_CANCELLED          = 13;
    const STATUS_RETURNED           = 17;

	protected $_name = 'lkp_audio_job_statuses';

	public function makeFetchSelect($db)
	{
		$select = $db->select();
		$select->from(array('lajst' => 'lkp_audio_job_statuses'));

		return $select;

	}

	public function fetchBySortOrder($sortOrder)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('lajst.sort_order = ?', $sortOrder);

		$results = $db->fetchRow($select);

		return $results;
	}

}