<?php

class m130219_131936_add_child_audiojob_complete_status extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('lkp_audio_job_statuses', 'is_child', 'TINYINT(1) DEFAULT 0');
        $this->insert('lkp_audio_job_statuses', array('id' => 26, 'name' => 'Multi part audio - returned with lead', 'sort_order' => '23', 'complete' => '1', 'is_child' => '1'));

        // Replicating Returned To Client status
        $this->insert('lkp_audio_job_statuses_rules', array('from_status_id' => 26, 'to_status_id' => 26));
        $this->insert('lkp_audio_job_statuses_rules', array('from_status_id' => 10, 'to_status_id' => 26));

        $this->insert('lkp_audio_job_statuses_rules', array('from_status_id' => 26, 'to_status_id' => 21));
        $this->insert('lkp_audio_job_statuses_rules', array('from_status_id' => 26, 'to_status_id' => 19));
        $this->insert('lkp_audio_job_statuses_rules', array('from_status_id' => 26, 'to_status_id' => 4));
        $this->insert('lkp_audio_job_statuses_rules', array('from_status_id' => 26, 'to_status_id' => 15));
        $this->insert('lkp_audio_job_statuses_rules', array('from_status_id' => 26, 'to_status_id' => 16));
        $this->insert('lkp_audio_job_statuses_rules', array('from_status_id' => 26, 'to_status_id' => 20));
	}

	public function safeDown()
	{
        $this->update('audio_jobs', array('status_id' => '17'), 'status_id = 26');
        $this->delete('lkp_audio_job_statuses_rules', 'from_status_id = :id OR to_status_id = :id', array(':id' => 26));
        $this->delete('lkp_audio_job_statuses',       'id = :id', array(':id' => 26));
        $this->dropColumn('lkp_audio_job_statuses',   'is_child');
	}
}