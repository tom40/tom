<?php

/**
 * Provides functions shared by typist and proofreader shift mappers
 *
 */
class Application_Model_DefaultShiftMapper extends App_Db_Table
{

    /**
     * Fetch shift days (Mon-Sun) in a date or the week of the given date
     * with the number of shifts for each day included
     *
     * @param string $table
     * @param string $date
     * @param bool $weekView
     *
     * @return array
     */
    protected function fetchShiftDays($table, $date, $weekView = false)
    {
		$db     = $this->getAdapter();
		$select = $db->select();
		$select->from(array('d' => $table));
        $select->order('d.start_day_number');

        // Fetch for given date only
        if (!$weekView)
        {
            $givenDateDayNumber = date('N', strtotime($date));
            $select->where('d.start_day_number = ?', $givenDateDayNumber);
        }

        $results = $db->fetchAll($select);

        // Format shift headings
        $formattedShiftData = array();

        if (!empty($results))
        {
            foreach ($results as $shiftDay)
            {

                $dayName = $shiftDay['start_day'];
                $formattedShiftData[$shiftDay['start_day_number']]['date']     = date('dS M', strtotime("this week $dayName", strtotime($date)));
                $formattedShiftData[$shiftDay['start_day_number']]['start_day'] = $shiftDay['start_day'];
                if (!isset($formattedShiftData[$shiftDay['start_day_number']]['noShifts']))
                {
                    $formattedShiftData[$shiftDay['start_day_number']]['noShifts'] = 1;
                }
                else
                {
                    $formattedShiftData[$shiftDay['start_day_number']]['noShifts'] += 1;
                }
            }
        }

		return $formattedShiftData;
    }

    /**
     * Fetch shift times for a given date/week
     *
     * @param string $table
     * @param string $date
     * @param bool $weekView
     *
     * @return array
     */
    protected function fetchShiftTimes($table, $date = null, $weekView = false)
    {
		$db     = $this->getAdapter();
		$select = $db->select();
		$select->from(array('d' => $table));

        // Fetch for given date only
        if (!$weekView && !empty($date))
        {
            $givenDateDayNumber = date('N', strtotime($date));
            $select->where('d.start_day_number = ?', $givenDateDayNumber);
        }

        $select->order('d.start_day_number ASC');
        $results = $db->fetchAll($select);
        return $results;
    }

    /**
     * Fetch a specific shift by id
     *
     * @param string $table
     * @param int $id
     *
     * @return array
     */
    protected function fetchShift($table, $id)
    {
        $db = $this->getAdapter();
		$select = $db->select();
        $select->from(array('d' => $table));
        $select->where('d.id = ?', $id);
        return $db->fetchRow($select);
    }

    /**
     * Used by form to display available shifts for a given day
     *
     * @param string $dayName
     *
     * @return array
     */
    public function fetchShifts($dayName = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(array('d' => $this->_name), array('key' =>'id', 'value' => "CONCAT(start_day, ' ' , DATE_FORMAT(start_time,'%l:%i%p'), ' - ', end_day, ' ', DATE_FORMAT(end_time,'%l:%i%p'))"));

        if (!empty($dayName))
        {
            $select->where('start_day = ?', $dayName);
        }

        $results = $db->fetchAll($select);
		return $results;
	}

    /**
     * Get the shift ID for the next available shift type based on current time
     *
     * @return null|int
     */
    public function getCurrentOrNextShiftDay()
    {
        $db          = $this->getAdapter();
        $select      = $db->select();

        $select->from(array('t' => $this->_name))
            ->order('t.start_day_number')
            ->order('t.start_time ASC');
        $results = $db->fetchAll($select);

        $currentTime    = date('H:i');
        $currentDate    = date('Y-m-d');
        $generatedDates = array();
        if (!empty($results))
        {
            foreach($results as $result)
            {
                $startDate = date('Y-m-d', strtotime(strtolower($result['start_day'])));
                $endDate   = date('Y-m-d', strtotime(strtolower($result['end_day'])));
                $startTime = date('H:i',   strtotime($result['start_time']));
                $endTime   = date('H:i',   strtotime($result['end_time']));

                $dateKey = $startDate. ' ' . $startTime . ' ' . $endDate . ' ' . $endTime;
                if ($startDate == $currentDate)
                {
                    if ($currentTime >= $startTime && $currentTime <= $endTime)
                    {
                        $generatedDates[$dateKey] = $result;
                    }
                    else if ($currentTime < $endTime)
                    {
                        $generatedDates[$dateKey] = $result;
                    }
                }
                else
                {
                    $generatedDates[$dateKey] = $result;
                }
            }

            ksort($generatedDates);
            $shift = array_shift($generatedDates);
            return $shift['id'];
        }
        return null;
    }

    /**
     * Get a date range from a shift type ID
     *
     * Audio jobs are not assigned to a shift but to a shift type, this work around will help discover
     * which shift an audio job is assigned to
     *
     * Method returns a date range
     *
     * @param int $shiftId Shift type ID
     *
     * @return array
     */
    public function getShiftDatRange($shiftId)
    {
        $shift = $this->fetchShift($this->_name, $shiftId);

        if (date('l') !== $shift['end_day'])
        {
            $end = strtotime('Next ' . $shift['end_day'] . ' ' . $shift['end_time']);
        }
        else
        {
            $end = strtotime('Today ' . $shift['end_time']);
        }

        $start = $end - ((60*60)*24)*7;

        return array(
            'start' => date('Y-m-d H:i:s', $start),
            'end'   => date('Y-m-d H:i:s', $end)
        );
    }
}