<?php

class Application_Model_TypistsShiftMapper extends Application_Model_UsersShiftMapper
{
    const BOOKED_SHIFT_STATUS  = 'BOOKED';
    const HOLIDAY_SHIFT_STATUS = 'HOLIDAY';

	protected $_name = 'typists_shifts';

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
               ->joinLeft(array('d' => 'typists_default_shift'), 'd.id = t.shift_id', array('start_day', 'start_time', 'end_day', 'end_time'))
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
               ->joinLeft(array('d' => 'typists_default_shift'), 'd.id = t.shift_id', array('shift_id' => 'id', 'start_time', 'end_time', 'on_holiday' => "IF (status = '".self::HOLIDAY_SHIFT_STATUS."', 1, '')"))
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
        $select->from(array('tp' => 'typists'),array('user_id','trained_summaries', 'trained_notes', 'trained_legal', 'full', 'note_taker', 'comments' => 'typing_comments', 'typing_speed'))
                ->joinInner(array('u' => 'users'), 'u.id = tp.user_id', array('name'))
                ->joinLeft(array('t' => $this->_name), "t.user_id = tp.user_id{$filterShiftSelect}", array('shift_date', 'shift_id', 'status', 'on_holiday' => "IF (status = '".self::HOLIDAY_SHIFT_STATUS."', 1, '')"))
                ->joinLeft(array('d' => 'typists_default_shift'), 'd.id = t.shift_id', array('default_shift_id' => 'id', 'start_day_number', 'startDayName' => 'start_day', 'end_day_number', 'endDayName' => 'end_day'))
                ->joinLeft(array('lp' => 'lkp_typist_grades'), 'lp.id = tp.grade_id', array('grade' => 'name'))
                ->where('u.active = ?', '1');

        $select = $this->_filterSelect($select, $filter);
        $select = $this->_filterShiftId($select, $filter);

        if (isset($filter['ability']))
        {
            $whereSt = array();
            foreach ($filter['ability'] as $ability)
            {
                $whereSt[] = "tp.$ability = 1";
            }

            $abilityWhereCondition = implode(' OR ', $whereSt);
            $select->where($abilityWhereCondition);
        }

        if (isset($filter['grade']))
        {
            $whereSt = array();
            foreach ($filter['grade'] as $grade)
            {
                $whereSt[] = "tp.grade_id = " . $grade;
            }

            $gradeWhereCondition = implode(' OR ', $whereSt);
            $select->where($gradeWhereCondition);
        }

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
    public function clearDateShifts($userId, $shiftDate, $excludeShifts = null)
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
               ->joinInner(array('t' => 'typists_default_shift'), 't.id = s.shift_id', array('start_time', 'end_time'))
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
		$select->from(array('s' => 'typists_default_shift'))
               ->where('s.id = ?', $id);
        return $db->fetchRow($select);
    }

    /**
     * Return user shifts by date / week
     *
     * @param int    $shiftId       Shift ID
     * @param string $name          Users name
     * @param array  $trainingCodes Optional list of training codes
     *
     * @return array
     */
    public function fetchUsersOnShift( $shiftId, $name = null, $trainingCodes = array() )
	{
        $shift         = $this->fetchDefaultShiftById($shiftId);
        $shiftStartDay = strtolower($shift['start_day']);
        $shiftDate     = date('Y-m-d', strtotime("$shiftStartDay"));
        $db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('tp' => 'typists'),array('user_id','trained_summaries', 'trained_notes', 'trained_legal', 'full', 'note_taker', 'comments' => 'typing_comments', 'typing_speed', 'payrate_id'))
                ->joinInner(array('u' => 'users'), 'u.id = tp.user_id', array('name'))
                ->joinLeft(array('t' => $this->_name), "t.user_id = tp.user_id", array())
                ->joinLeft(array('d' => 'typists_default_shift'), 'd.id = t.shift_id', array())
                ->joinLeft(array('lp' => 'lkp_typist_grades'), 'lp.id = tp.grade_id', array('grade' => 'name'))
                ->where('t.shift_id = ?', $shiftId)
                ->where('u.active = ?', '1')
                ->where("t.shift_date = '{$shiftDate}'");
            if (!empty($name))
            {
                $select->where("u.name LIKE '%{$name}%'");
            }

        if ( count( $trainingCodes ) > 0 )
        {
            foreach ( $trainingCodes as $code )
            {
                $field = Application_Model_TypistMapper::abilitiesMap( $code, true );
                $select->where( "tp." . $field . "='1'" );
            }
        }

        $select->group('tp.id');
		$results = $db->fetchAll($select);
		return $results;
	}

    /**
     * Return user shifts by date / week
     *
     * @param int    $shiftId       Shift ID
     * @param string $name          Users name
     * @param array  $trainingCodes Optional list of training codes
     *
     * @return array
     */
    public function fetchUsersNotOnShift( $shiftId, $name = null, $trainingCodes = array())
	{
        $excludeUsers = $this->_fetchOnShiftExclusionList($shiftId);
        $db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('tp' => 'typists'),array('user_id','trained_summaries', 'trained_notes', 'trained_legal', 'full', 'note_taker', 'comments' => 'typing_comments', 'typing_speed', 'payrate_id'))
                ->joinInner(array('u' => 'users'), 'u.id = tp.user_id', array('name'))
                ->joinLeft(array('t' => $this->_name), "t.user_id = tp.user_id", array())
                ->joinLeft(array('d' => 'typists_default_shift'), 'd.id = t.shift_id', array())
                ->joinLeft(array('lp' => 'lkp_typist_grades'), 'lp.id = tp.grade_id', array('grade' => 'name'))
                ->where('u.active = ?', '1');

        if (!empty($excludeUsers))
        {
            $select->where("u.id NOT IN ({$excludeUsers}) OR t.shift_id IS NULL");
        }

        if (!empty($name))
        {
            $select->where("u.name LIKE '%{$name}%'");
        }

        if ( count( $trainingCodes ) > 0 )
        {
            foreach ( $trainingCodes as $code )
            {
                $field = Application_Model_TypistMapper::abilitiesMap( $code, true );
                $select->where( "tp." . $field . "='1'" );
            }
        }

        $select->group('tp.id');
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
        $select->from(array('tp' => 'typists'),array('excludeUsers' => "GROUP_CONCAT(u.id SEPARATOR ',')"))
                ->joinInner(array('u' => 'users'), 'u.id = tp.user_id', array())
                ->joinLeft(array('t' => $this->_name), "t.user_id = tp.user_id", array())
                ->joinLeft(array('d' => 'typists_default_shift'), 'd.id = t.shift_id', array())
                ->joinLeft(array('lp' => 'lkp_typist_grades'), 'lp.id = tp.grade_id', array())
                ->where('t.shift_id = ?', $shiftId)
                ->where("t.shift_date = '{$shiftDate}'");
		$excludeUsers = $db->fetchOne($select);
        return $excludeUsers;
    }
}

