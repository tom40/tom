<?php

class m130117_103329_completedAudioJobStatus extends CDbMigration
{

	public function safeUp()
	{
        return $this->update('lkp_audio_job_statuses', array('complete' => '1'), "name = 'Returned to client'");
	}

	public function safeDown()
	{
        return $this->update('lkp_audio_job_statuses', array('complete' => '0'), "name = 'Returned to client'");
	}
}