<?php

class m121031_105518_update_proofreader_shifts extends CDbMigration
{
	public function safeUp()
	{
        $this->_removeAllShifts();
        $this->_addShiftTime(1,1,'Monday', '07:00', 1, 'Monday', '10:00');
        $this->_addShiftTime(2,1,'Monday', '06:00', 1, 'Monday', '10:00');
        $this->_addShiftTime(3,1,'Monday', '05:00', 1, 'Monday', '08:00');

        $this->_addShiftTime(4,2,'Tuesday', '07:00', 2, 'Tuesday', '10:00');
        $this->_addShiftTime(5,2,'Tuesday', '06:00', 2, 'Tuesday', '10:00');
        $this->_addShiftTime(6,2,'Tuesday', '05:00', 2, 'Tuesday', '08:00');

        $this->_addShiftTime(7,3,'Wednesday', '07:00', 3, 'Wednesday', '10:00');
        $this->_addShiftTime(8,3,'Wednesday', '06:00', 3, 'Wednesday', '10:00');
        $this->_addShiftTime(9,3,'Wednesday', '05:00', 3, 'Wednesday', '08:00');

        $this->_addShiftTime(10,4,'Thursday', '07:00', 4, 'Thursday', '10:00');
        $this->_addShiftTime(11,4,'Thursday', '06:00', 4, 'Thursday', '10:00');
        $this->_addShiftTime(12,4,'Thursday', '05:30', 4, 'Thursday', '10:30');

        $this->_addShiftTime(13,5,'Friday', '07:00', 5, 'Friday', '10:00');
        $this->_addShiftTime(14,5,'Friday', '06:00', 5, 'Friday', '10:00');
        $this->_addShiftTime(15,5,'Friday', '05:30', 5, 'Friday', '10:30');

        $this->_addShiftTime(16,6,'Saturday', '08:00', 7, 'Sunday', '19:00');
	}

	public function safeDown()
	{
        $this->_removeAllShifts();
        $this->_addShiftTime(1,1,'Monday', '07:00', null, null, '10:00');
        $this->_addShiftTime(2,2,'Tuesday', '07:00', null, null, '10:00');
        $this->_addShiftTime(3,3,'Wednesday', '07:00', null, null, '10:00');
        $this->_addShiftTime(4,4,'Thursday', '07:00', null, null, '10:00');
        $this->_addShiftTime(5,5,'Friday', '07:00', null, null, '10:00');
        $this->_addShiftTime(6,1,'Monday', '06:00', null, null, '10:00');
        $this->_addShiftTime(7,2,'Tuesday', '06:00', null, null, '10:00');
        $this->_addShiftTime(8,3,'Wednesday', '06:00', null, null, '10:00');
        $this->_addShiftTime(9,4,'Thursday', '06:00', null, null, '10:00');
        $this->_addShiftTime(10,5,'Friday', '06:00', null, null, '10:00');

        $this->_addShiftTime(11,1,'Monday', '05:00', null, null, '08:00');
        $this->_addShiftTime(12,2,'Tuesday', '05:00', null, null, '08:00');
        $this->_addShiftTime(13,3,'Wednesday', '05:00', null, null, '08:00');

        $this->_addShiftTime(14,4,'Thursday', '05:30', null, null, '10:30');
        $this->_addShiftTime(15,5,'Friday', '05:30', null, null, '10:30');

        $this->_addShiftTime(16,6,'Saturday', '08:00', null, null, '23:59');
        $this->_addShiftTime(17,7,'Sunday', '00:00', null, null, '19:00');

	}

    protected function _addShiftTime($id, $startDayNumber, $startDay, $startTime, $endDayNumber = null, $endDay = null, $endTime)
    {
        $this->insert('proofreaders_default_shift',
            array('id' => $id,'start_day_number' => $startDayNumber,'start_day' => $startDay, 'start_time' => $startTime, 'end_day_number' => $endDayNumber, 'end_day' => $endDay, 'end_time' => $endTime));
    }

    protected function _removeAllShifts()
    {
        $this->delete('proofreaders_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 1));
        $this->delete('proofreaders_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 2));
        $this->delete('proofreaders_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 3));
        $this->delete('proofreaders_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 4));
        $this->delete('proofreaders_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 5));
        $this->delete('proofreaders_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 6));
        $this->delete('proofreaders_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 7));
    }
}