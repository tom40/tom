<?php

class m130902_184907_staff_statuses extends CDbMigration
{
	public function safeUp()
	{
        $query = "update lkp_audio_job_statuses set `sort_order` = `sort_order` + 2 where `sort_order` > 18";
        $this->execute( $query );

        $this->insert('lkp_audio_job_statuses', array(
            'id'                   => 32,
            'name'                 => 'Rejected (for Invoice)',
            'proofreader_editable' => 0,
            'typist_editable'      => 0,
            'sort_order'           => 19,
            'complete'             => 1
        ));
        $this->insert('lkp_audio_job_statuses', array(
            'id'                   => 33,
            'name'                 => 'Accepted (for Invoice)',
            'proofreader_editable' => 0,
            'typist_editable'      => 0,
            'sort_order'           => 20,
            'complete'             => 1
        ));
	}

	public function safeDown()
	{
        $this->delete('lkp_audio_job_statuses', 'id = :id', array(':id' => 32));
        $this->delete('lkp_audio_job_statuses', 'id = :id', array(':id' => 33));

        $query = "update lkp_audio_job_statuses set `sort_order` = `sort_order` - 2 where `sort_order` > 18";
        $this->execute( $query );
	}
}