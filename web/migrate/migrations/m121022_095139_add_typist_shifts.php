<?php

class m121022_095139_add_typist_shifts extends CDbMigration
{
    public function safeUp()
	{
        $this->_removeDummayData();
        $this->_addShiftTime(1,1,'monday', '10:00', '16:00');
        $this->_addShiftTime(2,1,'monday', '18:00', '00:00');
        $this->_addShiftTime(3,2,'tuesday', '00:00', '08:00');
        $this->_addShiftTime(4,2,'tuesday', '10:00', '16:00');
        $this->_addShiftTime(5,2,'tuesday', '18:00', '00:00');
        $this->_addShiftTime(6,2,'tuesday', '00:00', '08:00');
        $this->_addShiftTime(7,3,'wednesday', '10:00', '16:00');
        $this->_addShiftTime(8,3,'wednesday', '18:00', '00:00');
        $this->_addShiftTime(9,4,'thursday', '00:00', '08:00');
        $this->_addShiftTime(10,4,'thursday', '10:00', '16:00');
        $this->_addShiftTime(11,4,'thursday', '18:00', '00:00');
        $this->_addShiftTime(12,5,'friday', '00:00', '08:00');
        $this->_addShiftTime(13,5,'friday', '10:00', '16:00');
        $this->_addShiftTime(14,5,'friday', '18:00', '00:00');
        $this->_addShiftTime(15,6,'saturday', '00:00', '08:00');
        $this->_addShiftTime(16,5,'friday', '18:00', '00:00');
        $this->_addShiftTime(17,6,'saturday','Saturday', '00:00', '00:00');
        $this->_addShiftTime(18,7,'sunday', '00:00', '12:00');
        $this->_addShiftTime(19,7,'sunday','12:00', '00:00');
        $this->_addShiftTime(20,1,'monday', '00:00', '08:00');
	}

    protected function _addShiftTime($id, $dayNumber, $dayName, $startTime, $endTime)
    {
        $this->insert('typists_default_shift',
            array('id' => $id,'day_number' => $dayNumber,'day_name' => $dayName, 'start_time' => $startTime, 'end_time' => $endTime));
    }

	public function safeDown()
	{
        $this->_removeDummayData();
	}

    protected function _removeDummayData()
    {
        $this->delete('typists_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 1));
        $this->delete('typists_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 2));
        $this->delete('typists_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 3));
        $this->delete('typists_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 4));
        $this->delete('typists_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 5));
        $this->delete('typists_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 6));
        $this->delete('typists_default_shift', 'day_number = :dayNumber', array(':dayNumber' => 7));
    }
}