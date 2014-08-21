<?php

class m130226_115604_add_part_assigned_to_typist_status extends CDbMigration
{
	public function safeUp()
	{
        $this->insert('lkp_audio_job_statuses', array('id' => 27, 'name' => 'Part assigned to typist', 'sort_order' => '24'));

        // You can go to Assigned to typist from here
        $this->insert('lkp_audio_job_statuses_rules', array('from_status_id' => 27, 'to_status_id' => 5));
	}

	public function safeDown()
	{
        $this->update('audio_jobs', array('status_id' => '3'), 'status_id = 27');
        $this->delete('lkp_audio_job_statuses_rules', 'from_status_id = :id OR to_status_id = :id', array(':id' => 27));
        $this->delete('lkp_audio_job_statuses',       'id = :id', array(':id' => 27));
	}
}