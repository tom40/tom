<?php

/**
 * @todo: proofreader and typists models have allot in common need to add as much as possible in UsersShiftMapper
 * once the Admin usage of UsersShiftMapper is known.
 */
class Application_Model_ProofreadersShiftMapper extends Application_Model_UsersShiftMapper
{
	protected $_name = 'proofreaders_shifts';

    /**
     * Return shifts by the given user
     *
     * @param int $userId
     *
     * @return array
     */
    public function fetchUserShifts($userId)
	{
		$db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('t' => $this->_name))
               ->joinLeft(array('d' => 'proofreaders_default_shift'), 'd.id = t.shift_id', array('start_day', 'start_time', 'end_day', 'end_time'))
               ->where('t.user_id = ?', $userId);
		$results = $db->fetchAll($select);
		return $results;
	}

     /**
     * Return shifts by users for given date and user
     *
     * @param int    $userId
     * @param string $shiftDate
     *
     * @return array
     */
    public function fetchDateShifts($userId, $shiftDate)
	{
		$db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('t' => $this->_name), array('shift_time' => 'shift_id', 'on_holiday' => "IF (status = '".self::HOLIDAY_SHIFT_STATUS."', 1, '')"))
               ->where('t.user_id = ?', $userId)
               ->where('t.shift_date = ?', $shiftDate);
		$results = $db->fetchAll($select);
		return $results;
	}

    /**
     * Return shift times for shifts by user and date
     *
     * @param int    $userId
     * @param string $shiftDate
     *
     * @return array
     */
    public function fetchShiftTimesByDate($userId, $shiftDate)
	{
		$db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('t' => $this->_name), array())
               ->joinLeft(array('d' => 'proofreaders_default_shift'), 'd.id = t.shift_id', array('shift_id' => 'id', 'start_time', 'end_time', 'on_holiday' => "IF (status = '".self::HOLIDAY_SHIFT_STATUS."', 1, '')"))
               ->where('t.user_id = ?', $userId)
               ->where('t.shift_date = ?', $shiftDate);
		$results = $db->fetchAll($select);
		return $results;
	}

    /**
     * Return user shifts by date / week
     *
     * @param string $shiftDate
     * @param array  $filter
     * @param bool   $weekView
     *
     * @return array
     */
    public function fetchShiftsByDate($shiftDate, $filter, $weekView = false)
	{
        $filterShiftSelect = $this->filterExistingShifts($shiftDate, $filter, $weekView);
        $db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('pr' => 'proofreaders'),array('user_id', 'comments' => 'proofreading_comments'))
                ->joinInner(array('u' => 'users'), 'u.id = pr.user_id', array('name'))
                ->joinLeft(array('t' => $this->_name), "t.user_id = pr.user_id{$filterShiftSelect}", array('shift_date', 'shift_id', 'status', 'on_holiday' => "IF (status = '".self::HOLIDAY_SHIFT_STATUS."', 1, '')"))
                ->joinLeft(array('d' => 'proofreaders_default_shift'), 'd.id = t.shift_id', array('default_shift_id' => 'id', 'start_day_number', 'startDayName' => 'start_day', 'end_day_number', 'endDayName' => 'end_day'))
                ->joinLeft(array('lp' => 'lkp_proofreader_grades'), 'lp.id = pr.grade_id', array('grade' => 'name'))
                ->where('u.active = ?', '1');

        $select = $this->_filterSelect($select, $filter);
        $select = $this->filterShiftStatus($select, $filter);

		$results = $db->fetchAll($select);
		return $this->filterShiftData($results);
	}

    /**
     * Remove shifts
     *
     * @param int $userId
     * @param string $shiftDate
     * @param array $excludeShifts
     *
     * @return void
     */
    public function clearDateShifts($userId, $shiftDate, $excludeShifts =  null)
    {
        $whereSt = "shift_date = '$shiftDate' AND user_id = " . $userId;
        if (!empty($excludeShifts))
        {
            $excludeShifts = implode(',', $excludeShifts);
            $whereSt .= " AND shift_id NOT IN ($excludeShifts)";
        }

        $this->delete($whereSt);
    }

    /**
     * Clear all shift data
     *
     * @param int $shiftId
     *
     * @return void
     */
    public function clearAllShiftData($shiftId)
    {
        $this->delete("shift_id = {$shiftId}");
    }

    public function save($data)
	{
		return parent::save($data);
	}

    /**
     * Fetch shifts by shift id
     *
     * @param int $id
     *
     * @return array
     */
    public function fetchShiftById($id)
    {
		$db     = $this->getAdapter();
		$select = $db->select();
		$select->from(array('s' => $this->_name))
               ->joinInner(array('p' => 'proofreaders_default_shift'), 'p.id = s.shift_id', array('start_time', 'end_time'))
               ->where('s.id = ?', $id);
        return $db->fetchRow($select);
    }

    /**
     * Fetch default shift id
     *
     * @todo: refactor: move to default shift mappers
     *
     * @param int $id
     *
     * @return array
     */
    public function fetchDefaultShiftById($id)
    {
		$db     = $this->getAdapter();
		$select = $db->select();
		$select->from(array('s' => 'proofreaders_default_shift'))
               ->where('s.id = ?', $id);
        return $db->fetchRow($select);
    }

    /**
     * Return user shifts by date / week
     *
     * @param string $shiftDate
     * @param array  $filter
     * @param bool   $weekView
     *
     * @return array
     */
    public function fetchUsersOnShift($shiftId, $name = null)
	{
        $shift         = $this->fetchDefaultShiftById($shiftId);
        $shiftStartDay = strtolower($shift['start_day']);
        $shiftDate     = date('Y-m-d', strtotime("$shiftStartDay"));
        $db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('tp' => 'proofreaders'),array('user_id','comments' => 'proofreading_comments'))
                ->joinInner(array('u' => 'users'), 'u.id = tp.user_id', array('name'))
                ->joinLeft(array('t' => $this->_name), "t.user_id = tp.user_id", array())
                ->joinLeft(array('d' => 'proofreaders_default_shift'), 'd.id = t.shift_id', array())
                ->joinLeft(array('lp' => 'lkp_proofreader_grades'), 'lp.id = tp.grade_id', array('grade' => 'name'))
                ->where('t.shift_id = ?', $shiftId)
                ->where("t.shift_date = '{$shiftDate}'")
                ->where('u.active = ?', '1');

            if (!empty($name))
            {
                $select->where("u.name LIKE '%{$name}%'");
            }

        $select->group('u.id');
		$results = $db->fetchAll($select);
		return $results;
	}

    /**
     * Return user shifts by date / week
     *
     * @param string $shiftDate
     * @param array  $filter
     * @param bool   $weekView
     *
     * @return array
     */
    public function fetchUsersNotOnShift($shiftId, $name = null)
	{
        $excludeUsers = $this->_fetchOnShiftExclusionList($shiftId);
        $db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('tp' => 'proofreaders'),array('user_id','comments' => 'proofreading_comments'))
                ->joinInner(array('u' => 'users'), 'u.id = tp.user_id', array('name'))
                ->joinLeft(array('t' => $this->_name), "t.user_id = tp.user_id", array())
                ->joinLeft(array('d' => 'proofreaders_default_shift'), 'd.id = t.shift_id', array())
                ->joinLeft(array('lp' => 'lkp_proofreader_grades'), 'lp.id = tp.grade_id', array('grade' => 'name'));
            if (!empty($excludeUsers))
            {
                $select->where("u.id NOT IN ({$excludeUsers}) OR t.shift_id IS NULL");
            }

            if (!empty($name))
            {
                $select->where("u.name LIKE '%{$name}%'");
            }

        $select->where('u.active = ?', '1');
        $select->group('u.id');
		$results = $db->fetchAll($select);
		return $results;
	}

    protected function _fetchOnShiftExclusionList($shiftId)
    {
        $shift         = $this->fetchDefaultShiftById($shiftId);
        $shiftStartDay = strtolower($shift['start_day']);
        $shiftDate     = date('Y-m-d', strtotime("$shiftStartDay"));
        $db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('tp' => 'proofreaders'),array('excludeUsers' => "GROUP_CONCAT(u.id SEPARATOR ',')"))
                ->joinInner(array('u' => 'users'), 'u.id = tp.user_id', array())
                ->joinLeft(array('t' => $this->_name), "t.user_id = tp.user_id", array())
                ->joinLeft(array('d' => 'proofreaders_default_shift'), 'd.id = t.shift_id', array())
                ->joinLeft(array('lp' => 'lkp_proofreader_grades'), 'lp.id = tp.grade_id', array())
                ->where('t.shift_id = ?', $shiftId)
                ->where("t.shift_date = '{$shiftDate}'");
		$excludeUsers = $db->fetchOne($select);
        return $excludeUsers;
    }
}

