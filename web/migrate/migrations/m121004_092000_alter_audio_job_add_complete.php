<?php

class m121004_092000_alter_audio_job_add_complete extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('lkp_audio_job_statuses', 'complete', 'TINYINT(1) DEFAULT 0');
        $this->update('lkp_audio_job_statuses', array('complete' => 1), 'id = :id', array('id' => 7));
        $this->update('lkp_audio_job_statuses', array('complete' => 1), 'id = :id', array('id' => 8));
	}

	public function safeDown()
	{
        $this->dropColumn('lkp_audio_job_statuses', 'complete');
	}
}