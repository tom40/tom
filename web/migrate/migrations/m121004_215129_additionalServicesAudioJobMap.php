<?php

class m121004_215129_additionalServicesAudioJobMap extends CDbMigration
{
	public function safeUp()
	{
        $this->createTable('additional_services_audio_jobs', array(
                'id'           => 'INT(11) NOT NULL AUTO_INCREMENT',
                'service_id'   => "INT(11) NOT NULL",
                'audio_job_id' => "INT(11)",
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
        $this->dropTable('additional_services_audio_jobs');
	}
}