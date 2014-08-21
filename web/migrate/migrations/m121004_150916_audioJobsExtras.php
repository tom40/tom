<?php

class m121004_150916_audioJobsExtras extends CDbMigration
{
	public function safeUp()
	{
        $this->createTable('audio_jobs_extras', array(
                'id'             => 'INT(11) NOT NULL AUTO_INCREMENT',
                'audio_job_id'   => "INT(11) NOT NULL",
                'description'    => "TEXT",
                'price'          => 'DECIMAL(9,4)',
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
        $this->dropTable('audio_jobs_extras');
	}
}