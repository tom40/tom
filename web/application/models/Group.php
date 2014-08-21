<?php

/**
 * Group Email
 *
 */
class Application_Model_Group extends App_Db_Table
{
    const USER_TYPE_TYPIST         = 'typist';
    const USER_TYPE_PROOFREADER    = 'proofreader';
    const FILTER_TYPIST_GRADE      = 'typist_grade';
    const FILTER_PROOFREADER_GRADE = 'proofreader_grade';
    const FILTER_SHIFT             = 'shift';
    const FILTER_TRAINING          = 'training';
    const FILTER_ALL               = 'all';
    const FILTER_PR_SHIFT_ID       = 'proofreader_shift_id';
    const FILTER_TYPIST_SHIFT_ID   = 'typist_shift_id';
    const AUDIO_JOB_APPROVED       = 2;
    const AUDIO_JOB_UNASSIGNED     = 3;

    /**
     * Filter options
     *
     * @var array
     */
    private $_options;

    protected $_includeUsersOnHoliday = true;

    /**
     * Returns a list of typists
     *
     * @param bool $includeInactive If true, include inactive typists
     *
     * @return array
     */
	public function getTypists($includeInactive = false)
    {
        $db     = $this->getAdapter();
		$select = $db->select();
		$select->from(array('t'       => 'typists'));
		$select->joinInner(array('u'  => 'users'), 't.user_id = u.id', array('user_id' => 'id', 'user_name' => 'name', 'email' => 'email'));
		$select->joinLeft(array('ltg' => 'lkp_typist_grades'), 't.grade_id = ltg.id', array('typing_grade' => 'name'));
        $select  = $this->_filterGrade($select);
        $select  = $this->_filterTraining($select);
        if (isset($this->_options[self::FILTER_TYPIST_SHIFT_ID]))
        {
            $shiftId = $this->_options[self::FILTER_TYPIST_SHIFT_ID];
            $select  = $this->_filterShift($select, self::USER_TYPE_TYPIST, $shiftId);
        }
        else
        {
            $select  = $this->_filterShift($select);
        }

        if (false === $includeInactive)
        {
            $select->where('u.active = ?', '1');
        }

        $select  = $this->_filterHoliday($select);
        $select->order('u.name ASC');
        $typists = $db->fetchAll($select);

        return $typists;
    }

    /**
     * Returns a list of proofreaders
     *
     * @param bool $includeInactive If true, include inactive proofreaders
     *
     * @return array
     */
	public function getProofreaders($includeInactive = false)
    {
        $db     = $this->getAdapter();
		$select = $db->select();
		$select->from(array('p'       => 'proofreaders'));
		$select->joinInner(array('u'  => 'users'), 'p.user_id = u.id', array('user_id' => 'id', 'user_name' => 'name', 'email' => 'email'));
		$select->joinLeft(array('lpg' => 'lkp_proofreader_grades'), 'p.grade_id = lpg.id', array('proofreader_grade' => 'name'));
        $select  = $this->_filterGrade($select, self::USER_TYPE_PROOFREADER);
        if (isset($this->_options[self::FILTER_PR_SHIFT_ID]))
        {
            $shiftId = $this->_options[self::FILTER_PR_SHIFT_ID];
            $select  = $this->_filterShift($select, self::USER_TYPE_PROOFREADER, $shiftId);
        }
        else
        {
            $select  = $this->_filterShift($select, self::USER_TYPE_PROOFREADER);
        }

        if (false === $includeInactive)
        {
            $select->where('u.active = ?', '1');
        }
        $select  = $this->_filterHoliday($select, 'proofreaders_shifts');
        $select->order('u.name ASC');
        $typists = $db->fetchAll($select);

        return $typists;
    }

    /**
     * Remove when the user is marked as holiday from the results retured
     *
     * @param Zend_Db_Adapter_Abstract $select
     * @param string $table
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _filterHoliday($select, $table = 'typists_shifts')
    {
        if (!$this->_includeUsersOnHoliday)
        {
            $shiftDate = date('Y-m-d');
            $select->where("u.id NOT IN(SELECT user_id FROM $table WHERE shift_date = '$shiftDate' AND status = 'HOLIDAY')");
        }
        return $select;
    }

    /**
     * Filter by grade
     *
     * @param Zend_Db_Adapter_Abstract $select
     * @param string                   $userType
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _filterGrade($select, $userType = self::USER_TYPE_TYPIST)
    {
        $gradeName = 'typist_grade';
        if ($userType == self::USER_TYPE_PROOFREADER)
        {
            $gradeName = 'proofreader_grade';
        }

        if (isset($this->_options[$gradeName]))
        {
            $grades = $this->_options[$gradeName];
            if (!empty($grades) && !in_array(self::FILTER_ALL, $grades))
            {
                $grades = implode(',', $grades);
                $select->where("grade_id IN($grades)");
            }
        }

        return $select;
    }

    /**
     * Filter by training
     *
     * @param Zend_Db_Adapter_Abstract $select
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _filterTraining($select)
    {
        if (isset($this->_options[self::FILTER_TRAINING]))
        {
            $trainedIn           = $this->_options[self::FILTER_TRAINING];
            $whereStatement      = '';
            $firstItem           = true;
            $displayAllAbilities = false;
            foreach($trainedIn as $trainingType)
            {
                if (self::FILTER_ALL !== $trainingType)
                {
                    if (false === $firstItem)
                    {
                        $whereStatement .= " OR ";
                    }

                    $whereStatement .= "{$trainingType} = 1";
                    $firstItem      = false;
                }
                else
                {
                    $displayAllAbilities = true;
                }
            }

            if (!$displayAllAbilities)
            {
                $select->where($whereStatement);
            }
        }

        return $select;
    }

    /**
     * Filter typist shift
     *
     * @param Zend_Db_Adapter_Abstract $select
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _filterShift($select, $userType = self::USER_TYPE_TYPIST, $shiftId = null)
    {
        $shiftMapper            = new Application_Model_UsersShiftMapper();
        if (isset($this->_options[self::FILTER_SHIFT]))
        {
            $shiftData              = $this->_options[self::FILTER_SHIFT];
            $onShiftToday           = in_array('today', $shiftData) ? true : false;
            $onShiftNotAssignedWork = in_array('not_assigned_work', $shiftData) ? true : false;
            $notOnShift             = in_array('not_on_shift', $shiftData) ? true : false;

            if ($userType == self::USER_TYPE_PROOFREADER)
            {
                $shiftTable = 'proofreaders_shifts';
                $jobTable   = 'audio_jobs_proofreaders';
            }
            else
            {
                $shiftTable = 'typists_shifts';
                $jobTable   = 'audio_jobs_typists';
            }

            $todaysDate = date('Y-m-d');
            $showAll = ($notOnShift && $onShiftToday && $onShiftNotAssignedWork)
            || ($notOnShift && $onShiftToday) ? true: false;

            if (!$showAll)
            {
                $shiftLimitQuery = '';
                if ($notOnShift)
                {
                    if ($onShiftNotAssignedWork)
                    {
                        if (!empty($shiftId))
                        {
                            $shiftLimitQuery = "AND us.shift_id = {$shiftId}";
                        }

                        $select->joinLeft(array('us' => $shiftTable), "us.user_id = u.id AND us.shift_date = '{$todaysDate}' {$shiftLimitQuery}");
                        $select->where("u.id NOT IN (SELECT user_id FROM {$jobTable} WHERE due_date LIKE '{$todaysDate}%' AND current = 1)");
                        $select->orwhere("u.id NOT IN
                            (SELECT user_id FROM {$shiftTable} us
                            WHERE us.shift_date = '{$todaysDate}'
                            AND status != 'HOLIDAY'
                            {$shiftLimitQuery}
                        )");
                    }
                    else
                    {
                        if (!empty($shiftId))
                        {
                            $shiftLimitQuery = " AND us.shift_id = {$shiftId}";
                        }
                        $select->where("u.id NOT IN
                                (SELECT user_id FROM {$shiftTable} us
                                WHERE us.shift_date = '{$todaysDate}'
                                AND status != 'HOLIDAY'
                                {$shiftLimitQuery}
                        )");
                    }
                }
                elseif ($onShiftToday)
                {

                    if (!empty($shiftId))
                    {
                        $shiftLimitQuery = "AND us.shift_id = {$shiftId}";
                    }
                    $select->joinInner(array('us' => $shiftTable), "us.user_id = u.id AND us.shift_date = '{$todaysDate}'{$shiftLimitQuery}");
                    $this->_includeUsersOnHoliday = false;
                }
                elseif ($onShiftNotAssignedWork)
                {
                    if (!empty($shiftId))
                    {
                        $shiftLimitQuery = "AND us.shift_id = {$shiftId}";
                    }

                    $select->joinInner(array('us' => $shiftTable), "us.user_id = u.id AND us.shift_date = '{$todaysDate}' {$shiftLimitQuery}");
                    $select->where("u.id NOT IN (SELECT user_id FROM {$jobTable} WHERE due_date LIKE '{$todaysDate}%' AND current = 1)");
                    $this->_includeUsersOnHoliday = false;
                }
            }
        }
        return $select;
    }

    /**
     * Get Extra Work list (approved and unassigned)
     *
     * @param string $sortField
     *
     * @todo: re-factor
     *
     * @return array
     */
    public function getExtraWorkDetails($sortField='aj.id')
	{
        $audioJobMapper = new Application_Model_AudioJobMapper();
        $db             = $this->getAdapter();
        $select         = $audioJobMapper->makeFetchSelect($db);
		$select->where('aj.completed = 0')
            ->where('aj.archived = 0')
            ->where('aj.lead_id IS NULL')
            ->where('aj.status_id = ' . self::AUDIO_JOB_APPROVED . ' OR aj.status_id = ' . self::AUDIO_JOB_UNASSIGNED);
		$select->order($sortField);

		$results = $db->fetchAll($select);

        $sql = 'DROP TEMPORARY TABLE IF EXISTS tmp_audio_typists';
		$db->query($sql);
		$sql = 'DROP TEMPORARY TABLE IF EXISTS tmp_audio_proofreaders';
		$db->query($sql);
		return $results;
	}

    /**
     * Set a filter for the data we are fetching
     *
     * @param string $name  the name of the filter
     * @param string $value the value
     */
    public function setFilter($name, $value)
    {
        $this->_options[$name] = $value;
    }
}

