<?php

class m130226_120533_add_part_assigned_to_proofreader_status extends CDbMigration
{
	public function safeUp()
	{
        $this->insert('lkp_audio_job_statuses', array('id' => 28, 'name' => 'Part assigned to proofreader', 'sort_order' => '25'));

        // You can go to Assigned to proofreader from here
        $this->insert('lkp_audio_job_statuses_rules', array('from_status_id' => 28, 'to_status_id' => 9));
	}

	public function safeDown()
	{
        $this->update('audio_jobs', array('status_id' => '3'), 'status_id = 28');
        $this->delete('lkp_audio_job_statuses_rules', 'from_status_id = :id OR to_status_id = :id', array(':id' => 28));
        $this->delete('lkp_audio_job_statuses',       'id = :id', array(':id' => 28));
	}
}