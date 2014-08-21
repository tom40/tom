<?php

class m140219_045555_poor_audio_flag extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn( 'audio_jobs', 'poor_audio', "ENUM('0','1') DEFAULT '0' AFTER internal_comments" );
        $this->addColumn( 'audio_jobs_shadow', 'poor_audio', "ENUM('0','1') DEFAULT '0' AFTER internal_comments" );
	}

	public function safeDown()
	{
        $this->dropColumn( 'audio_jobs', 'poor_audio' );
        $this->dropColumn( 'audio_jobs_shadow', 'poor_audio' );
	}
}