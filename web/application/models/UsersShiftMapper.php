<?php

class Application_Model_UsersShiftMapper extends App_Db_Table
{
	protected $_name         = 'users_shifts';
	protected $_useAcl       = true;
	protected $_shadowExists = false;

    const BOOKED_SHIFT_STATUS  = 'BOOKED';
    const HOLIDAY_SHIFT_STATUS = 'HOLIDAY';

    const TYPIST_SHIFT      = 'typist';
    const PROOFREADER_SHIFT = 'proofreader';

    /**
     * Filters select statement
     *
     * @param Select $select
     * @param array $filter
     *
     * @return Select
     */
    protected function _filterSelect($select, $filter)
    {
        if (isset($filter['name']))
        {
            $name = $filter['name'];
            $select->where("u.name LIKE '%$name%'");
        }
        return $select;
    }

    /**
     * Filter by shift id
     *
     * @param Select $select
     * @param array $filter
     */
    protected function _filterShiftId($select, $filter)
    {
        if (isset($filter['shift_id']))
        {
            $select->where('d.id = ?', $filter['shift_id']);
        }
        return $select;
    }

    /**
     * Filters select statement
     *
     * @param Select $select
     * @param array $filter
     *
     * @return Select
     */
    protected function _filterShiftSatus($filter)
    {
        $sql = '';
        if (isset($filter['shift_status']))
        {
            if (in_array('blank', $filter['shift_status']))
            {
                $key = array_search('blank', $filter['shift_status']);
                unset($filter['shift_status'][$key]);
            }

            if (!empty($filter['shift_status']))
            {
                // If other statuses are selected ignore blank
                $shiftStatuses = "'" . implode("','", $filter['shift_status']) . "'";
                $sql = " AND t.status IN ($shiftStatuses)";
            }
        }
        else
        {
            // Default shows bookings
            $sql = " AND t.status = ' ". self::BOOKED_SHIFT_STATUS ."'";
        }
        return $sql;
    }

    protected function filterShiftData($shiftData)
    {
        $filteredShiftData = array();
        if (!empty($shiftData))
        {
            // Filter for presentation
            foreach($shiftData as $shift)
            {
                $shiftId = !empty($shift['shift_id']) ? $shift['shift_id']: $shift['status'];

                // Set shift day number
                if (empty($shift['day_number']))
                {
                    $shift['day_number'] = date('N', strtotime($shift['shift_date']));
                }

                $userId = $shift['user_id'];

                $userInfo = array();
                $userInfo['name']     = $shift['name'];
                $userInfo['comments'] = $shift['comments'];
                $userInfo['grade']    = $shift['grade'];
                if (isset($shift['typing_speed']))
                {
                    $userInfo['typing_speed'] = $shift['typing_speed'];
                }

                $filteredShiftData[$userId]['info'] = $userInfo;
                $filteredShiftData[$userId]['shifts'][$shift['day_number']][$shiftId] = $shift;
            }
        }
        return $filteredShiftData;
    }

    /**
     * Fetches existing shifts with specified parameters
     *
     * @param type $shiftDate
     * @param type $filter
     * @param type $weekView
     * @return string
     */
    protected function filterExistingShifts($shiftDate, $filter, $weekView = false)
    {
        $sql = '';
        // Fetch date or week date
        if ($weekView)
        {
            $firstDayOfWeek = date('Y-m-d', strtotime('this week monday', strtotime($shiftDate)));
            $lastDayOfWeek  = date('Y-m-d', strtotime('this week sunday', strtotime($shiftDate)));
            $sql = "(t.shift_date >= '{$firstDayOfWeek}' AND t.shift_date <= '{$lastDayOfWeek}')";
        }
        else
        {
            $sql = "t.shift_date = '$shiftDate'";
        }

        $shiftStatusSelect = $this->_getShiftStatusFilterStatement($filter);
        if (!empty($sql) && !empty($shiftStatusSelect))
        {
            $sql .= ' AND ';
        }

        $sql .= $shiftStatusSelect;

        if (!empty($sql))
        {
            $sql = ' AND ( ' . $sql . ' )';
        }

        return $sql;
    }

    protected function _getShiftStatusFilterStatement($filter)
    {
        $sql = '';
        if (isset($filter['shift_status']))
        {
            if (in_array('blank', $filter['shift_status']))
            {
                $key = array_search('blank', $filter['shift_status']);
                unset($filter['shift_status'][$key]);
            }

            if (!empty($filter['shift_status']))
            {
                // If other statuses are selected ignore blank
                $shiftStatuses = "'" . implode("','", $filter['shift_status']) . "'";
                $sql .= "t.status IN ($shiftStatuses)";
            }
        }
        else
        {
            // Default shows bookings
            $sql = "t.status = '". self::BOOKED_SHIFT_STATUS ."'";
        }

        return $sql;
    }

    protected function filterShiftStatus($select, $filter)
    {
        if (isset($filter['shift_status']))
        {
            // if only BLANK is selected show only those shifts
            if (isset($filter['shift_status']) && 1 == count($filter['shift_status']) && in_array('blank', $filter['shift_status']))
            {
                $select->where('t.id IS NULL');
            }

            // If BLANK is not selected exclude blanks
            if (!in_array('blank', $filter['shift_status']))
            {
                $select->where('t.id IS NOT NULL');
            }
        }
        else
        {
            $select->where('t.id IS NOT NULL');
        }

        return $select;
    }

    protected function _getTypistsNotOnShift($startDate, $endDate)
    {
        $sql = "SELECT DISTINCT(t.user_id), u.email FROM typists t
        INNER JOIN users u ON u.id = t.user_id
        WHERE u.active = '1' AND t.user_id NOT IN(SELECT user_id FROM typists_shifts s WHERE (s.shift_date >= '$startDate' ) AND s.shift_date <= '$endDate')";
        $db = $this->getAdapter();
        return $db->fetchAll($sql);
    }

    protected function _getProofreadersNotOnShift($startDate, $endDate)
    {
        $sql = "SELECT DISTINCT(p.user_id), u.email FROM proofreaders p
        INNER JOIN users u ON u.id = p.user_id
        WHERE u.active = '1' AND p.user_id NOT IN(SELECT user_id FROM proofreaders_shifts s WHERE (s.shift_date >= '$startDate' ) AND s.shift_date <= '$endDate')";
        $db = $this->getAdapter();
        return $db->fetchAll($sql);
    }

    protected function _fetchShiftsNotInRange($select, $beforeDate)
    {
        $select->where('s.shift_date > ?', date('Y-m-d'));
        $select->where('s.shift_date < ?', $beforeDate);
        $select->group('s.user_id');
        return $select;
    }

    /**
     * Get users who have no shifts within a date range (used for fortnight check)
     *
     * @param string $startDate
     * @param string $endDate
     *
     * @return array
     */
    public function getUsersWithNoShift($startDate, $endDate)
    {
		$typistsNotOnShift      = $this->_getTypistsNotOnShift($startDate, $endDate);
        //$proofreadersNotOnShift = $this->_getProofreadersNotOnShift($startDate, $endDate);
        //$reminderList           = array_merge($typistsNotOnShift, $proofreadersNotOnShift);
        //return $reminderList;

        return $typistsNotOnShift;
    }

    /**
     * Get shift from user id and default shift id
     *
     * @return array
     */
    public function getShift($userId, $defaultShiftId)
    {
		$db     = $this->getAdapter();
		$select = $db->select();
		$select->from(array('s' => $this->_name))
            ->where('s.user_id = ?', $userId)
            ->where('s.shift_id = ?', $defaultShiftId);

        return $db->fetchRow($select);
    }
}

