<?php

class m130401_023058_audio_job_comments extends CDbMigration
{

	public function safeUp()
	{
        $this->alterColumn('audio_jobs_typists', 'comment', 'TEXT');
        $this->alterColumn('audio_jobs_proofreaders', 'comment', 'TEXT');
	}

	public function safeDown()
	{
        $this->alterColumn('audio_jobs_typists', 'comment', 'VARCHAR(255)');
        $this->alterColumn('audio_jobs_proofreaders', 'comment', 'VARCHAR(255)');
	}
}