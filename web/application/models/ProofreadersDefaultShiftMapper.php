<?php

class Application_Model_ProofreadersDefaultShiftMapper extends Application_Model_DefaultShiftMapper
{
	protected $_name = 'proofreaders_default_shift';

    public function fetchAvailableShiftDays($date = null, $weekView = false)
    {
        return $this->fetchShiftDays($this->_name, $date, $weekView);
    }

    public function fetchAvailableShiftTimes($date = null, $weekView = false)
    {
        return $this->fetchShiftTimes($this->_name, $date, $weekView);
    }

    public function fetchCurrentShift($id)
    {
        return $this->fetchShift($this->_name, $id);
    }

    /**
     * Deletes a shift and all related data.
     *
     * Probably best to delegate deleting to the appropriate objects
     * however to keep it simple all work is done here.
     *
     * @param int $shiftId
     *
     * @return void
     */
    public function removeShift($shiftId)
    {
        $db = $this->getAdapter();
        $db->delete($this->_name, 'id = ' . $shiftId);
        $db->delete('proofreaders_shifts', 'shift_id = ' . $shiftId);
        $db->delete('audio_jobs_proofreaders', 'shift_id = ' . $shiftId);
    }

        public function getShiftDaysForDropdown()
    {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('t' => $this->_name), array('key' =>'start_day_number', 'value' => 'start_day'));
        $select->group('t.start_day');
        $select->order('t.start_day_number');
        $results = $db->fetchAll($select);
        return $results;
    }

    public function getShiftTimesForDropdown($selectedDayNumber)
    {
		$db     = $this->getAdapter();
		$select = $db->select();

		$select->from(array('t' => $this->_name), array('key' => 'id', 'value' => "CONCAT(DATE_FORMAT(start_time,'%l:%i%p'), ' - ', DATE_FORMAT(end_time,'%l:%i%p'))"));
        $select->where('t.start_day_number = ?', $selectedDayNumber);
        $results = $db->fetchAll($select);
        return $results;
    }

    public function hasShiftTimes($selectedDayNumber)
    {
		$db     = $this->getAdapter();
		$select = $db->select();

		$select->from(array('t' => $this->_name), array('id'));
        $select->where('t.start_day_number = ?', $selectedDayNumber);
        $results = $db->fetchAll($select);
        if (!empty($results))
        {
            return true;
        }

        return false;
    }

    public function getFirstShiftDayNumber()
    {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('t' => $this->_name), array('start_day_number'));
        $select->group('t.start_day');
        $select->order('t.start_day_number');
        $select->limit(1);
        $results = $db->fetchOne($select);
        return $results;
    }
}