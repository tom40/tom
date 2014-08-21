<?php

class m130902_185835_last_status_audiojob extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn( 'audio_jobs', 'last_status_id', "INT AFTER `status_id`" );
        $this->addColumn( 'audio_jobs_shadow', 'last_status_id', "INT AFTER `status_id`" );
	}

	public function safeDown()
	{
        $this->dropColumn( 'audio_jobs', 'last_status_id' );
        $this->dropColumn( 'audio_jobs_shadow', 'last_status_id' );
	}
}