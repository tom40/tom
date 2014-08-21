<?php

class m121023_013307_add_proofreader_shifts extends CDbMigration
{
	    public function safeUp()
	{
        $this->_removeDummayData();
        $this->_addShiftTime(1,1,'monday', '07:00', '10:00');
        $this->_addShiftTime(2,2,'tuesday', '07:00', '10:00');
        $this->_addShiftTime(3,3,'wednesday', '07:00', '10:00');
        $this->_addShiftTime(4,4,'thursday', '07:00', '10:00');
        $this->_addShiftTime(5,5,'friday', '07:00', '10:00');

        $this->_addShiftTime(6,1,'monday', '06:00', '10:00');
        $this->_addShiftTime(7,2,'tuesday', '06:00', '10:00');
        $this->_addShiftTime(8,3,'wednesday', '06:00', '10:00');
        $this->_addShiftTime(9,4,'thursday', '06:00', '10:00');
        $this->_addShiftTime(10,5,'friday', '06:00', '10:00');

        $this->_addShiftTime(11,1,'monday', '05:00', '08:00');
        $this->_addShiftTime(12,2,'tuesday', '05:00', '08:00');
        $this->_addShiftTime(13,3,'wednesday', '05:00', '08:00');

        $this->_addShiftTime(14,4,'thursday', '05:30', '10:30');
        $this->_addShiftTime(15,5,'friday', '05:30', '10:30');

        $this->_addShiftTime(16,6,'saturday', '08:00', '00:00');
        $this->_addShiftTime(17,7,'sunday', '00:00', '19:00');
	}

    protected function _addShiftTime($id, $dayNumber, $dayName, $startTime, $endTime)
    {
        $this->insert('proofreaders_default_shift',
            array('id' => $id,'day_number' => $dayNumber,'day_name' => $dayName, 'start_time' => $startTime, 'end_time' => $endTime));
    }

	public function safeDown()
	{
        $this->_removeDummayData();
	}

    protected function _removeDummayData()
    {
        $this->delete('proofreaders_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 1));
        $this->delete('proofreaders_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 2));
        $this->delete('proofreaders_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 3));
        $this->delete('proofreaders_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 4));
        $this->delete('proofreaders_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 5));
        $this->delete('proofreaders_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 6));
        $this->delete('proofreaders_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 7));
    }
}