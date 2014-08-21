<?php

class m120921_092637_add_36hour_to_turnaroundtime extends CDbMigration
{
	public function safeUp()
	{
        $this->insert('lkp_turnaround_times', array('id' => 6, 'name' => '36 HOUR (summaries only)', 'sort_order' => 3));
        $this->update('lkp_turnaround_times', array('sort_order' => '4'), 'id = 1');
        $this->update('lkp_turnaround_times', array('sort_order' => '5'), 'id = 4');
        $this->update('lkp_turnaround_times', array('sort_order' => '6'), 'id = 5');
	}

	public function safeDown()
	{
        $this->delete('lkp_turnaround_times', 'id = :id', array(':id' => 6));
        $this->update('lkp_turnaround_times', array('sort_order' => '3'), 'id = 1');
        $this->update('lkp_turnaround_times', array('sort_order' => '4'), 'id = 4');
        $this->update('lkp_turnaround_times', array('sort_order' => '5'), 'id = 5');
	}
}