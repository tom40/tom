<?php

/**
 * Add dummay test data for default typist and proofreader shifts
 * 
 */
class m121017_155745_add_dummy_default_user_shift_data extends CDbMigration
{
    protected $_idCounter = 0;

    public function safeUp()
	{
        // Dummy Typists Default Shifts
        $this->_generateDefaultShiftData('typists_default_shift');
        $this->_generateDefaultShiftData('proofreaders_default_shift');
	}

	public function safeDown()
	{
        $this->_removeDummayData('typists_default_shift');
        $this->_removeDummayData('proofreaders_default_shift');
	}

    protected function _removeDummayData($table)
    {
        $this->delete($table, 'day_name = :dayName', array(':dayName' => 'Monday'));
        $this->delete($table, 'day_name = :dayName', array(':dayName' => 'Tuesday'));
        $this->delete($table, 'day_name = :dayName', array(':dayName' => 'Wednesday'));
        $this->delete($table, 'day_name = :dayName', array(':dayName' => 'Thursday'));
        $this->delete($table, 'day_name = :dayName', array(':dayName' => 'Friday'));
        $this->delete($table, 'day_name = :dayName', array(':dayName' => 'Saturday'));
        $this->delete($table, 'day_name = :dayName', array(':dayName' => 'Sunday'));

    }

    protected function _generateDefaultShiftData($table)
    {
        $this->_addDefaultDayData($table, 1, 'Monday');
        $this->_addDefaultDayData($table, 2, 'Tuesday');
        $this->_addDefaultDayData($table, 3, 'Wednesday');
        $this->_addDefaultDayData($table, 4, 'Thursday');
        $this->_addDefaultDayData($table, 5, 'Friday');
        $this->_addDefaultDayData($table, 6, 'Saturday');
        $this->_addDefaultDayData($table, 7, 'Sunday');
    }

    protected function _addDefaultDayData($table, $dayNumber, $dayName)
    {
        $this->insert($table,
            array('id' => $this->_getId(),'day_number' => $dayNumber,'day_name' => $dayName, 'start_time' => '09:00', 'end_time' => '10:00'));
        $this->insert($table,
            array('id' => $this->_getId(),'day_number' => $dayNumber,'day_name' => $dayName, 'start_time' => '10:00', 'end_time' => '11:00'));
        $this->insert($table,
            array('id' => $this->_getId(),'day_number' => $dayNumber,'day_name' => $dayName, 'start_time' => '13:00', 'end_time' => '14:00'));
    }

    protected function _getId()
    {
        $this->_idCounter++;
        return $this->_idCounter;
    }
}