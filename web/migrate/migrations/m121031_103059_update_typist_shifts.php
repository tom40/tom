<?php

class m121031_103059_update_typist_shifts extends CDbMigration
{
	public function safeUp()
	{
        $this->_removeAllShifts();
        $this->_addShiftTime(1,1,'Monday', '10:00', 1, 'Monday', '16:00');
        $this->_addShiftTime(2,1,'Monday', '18:00', 2, 'Tuesday', '08:00');

        $this->_addShiftTime(3,2,'Tuesday', '10:00', 2, 'Tuesday', '16:00');
        $this->_addShiftTime(4,2,'Tuesday', '18:00', 3, 'Wednesday', '08:00');


        $this->_addShiftTime(5,3,'Wednesday', '10:00', 3, 'Wednesday', '16:00');
        $this->_addShiftTime(6,3,'Wednesday', '18:00', 4, 'Thursday', '08:00');

        $this->_addShiftTime(7,4,'Thursday', '10:00', 4, 'Thursday', '16:00');
        $this->_addShiftTime(8,4,'Thursday', '18:00', 5, 'Friday', '08:00');

        $this->_addShiftTime(9,5,'Friday', '10:00', 5, 'Friday', '16:00');
        $this->_addShiftTime(10,5,'Friday', '18:00', 6, 'Saturday', '08:00');
        $this->_addShiftTime(11,5,'Friday', '18:00', 7, 'Sunday', '12:00');

        $this->_addShiftTime(12,7,'Sunday', '12:00', 7, 'Monday', '08:00');
	}

	public function safeDown()
	{
        $this->_removeAllShifts();
        $this->_addShiftTime(1,1,'Monday', '10:00', null, null, '16:00');
        $this->_addShiftTime(2,1,'Monday', '18:00', null, null, '23:59');
        $this->_addShiftTime(3,2,'Tuesday', '00:00', null, null, '08:00');

        $this->_addShiftTime(4,2,'Tuesday', '10:00', null, null, '16:00');
        $this->_addShiftTime(5,2,'Tuesday', '18:00', null, null, '23:59');
        $this->_addShiftTime(6,3,'Wednesday', '00:00', null, null, '08:00');

        $this->_addShiftTime(7,3,'Wednesday', '10:00', null, null, '16:00');
        $this->_addShiftTime(8,3,'Wednesday', '18:00', null, null, '23:59');
        $this->_addShiftTime(9,4,'Thursday', '00:00', null, null, '08:00');

        $this->_addShiftTime(10,4,'Thursday', '10:00', null, null, '16:00');
        $this->_addShiftTime(11,4,'Thursday', '18:00', null, null, '23:59');
        $this->_addShiftTime(12,5,'Friday', '00:00', null, null, '08:00');

        $this->_addShiftTime(13,5,'Friday', '10:00', null, null, '16:00');
        $this->_addShiftTime(14,5,'Friday', '18:00', null, null, '23:59');
        $this->_addShiftTime(15,6,'Saturday', '00:00', null, null, '08:00');

        $this->_addShiftTime(16,6,'Saturday', '08:00', null, null, '23:59');
        $this->_addShiftTime(17,7,'Sunday', '00:00', null, null, '12:00');

        $this->_addShiftTime(18,7,'Sunday', '12:00', null, null, '23:59');
        $this->_addShiftTime(19,1,'Monday', '00:00', null, null, '08:00');

	}

    protected function _addShiftTime($id, $startDayNumber, $startDay, $startTime, $endDayNumber = null, $endDay = null, $endTime)
    {
        $this->insert('typists_default_shift',
            array('id' => $id,'start_day_number' => $startDayNumber,'start_day' => $startDay, 'start_time' => $startTime, 'end_day_number' => $endDayNumber, 'end_day' => $endDay, 'end_time' => $endTime));
    }

    protected function _removeAllShifts()
    {
        $this->delete('typists_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 1));
        $this->delete('typists_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 2));
        $this->delete('typists_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 3));
        $this->delete('typists_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 4));
        $this->delete('typists_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 5));
        $this->delete('typists_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 6));
        $this->delete('typists_default_shift', 'start_day_number = :dayNumber', array(':dayNumber' => 7));
    }

}