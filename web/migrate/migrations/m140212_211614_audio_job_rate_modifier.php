<?php

class m140212_211614_audio_job_rate_modifier extends CDbMigration
{

	public function safeUp()
    {
        $this->addColumn( 'audio_jobs', 'interval_rate_modifier', "DECIMAL(8,4) AFTER `rate`" );
        $this->addColumn( 'audio_jobs_shadow', 'interval_rate_modifier', "DECIMAL(8,4)" );
	}

	public function safeDown()
	{
        $this->dropColumn( 'audio_jobs', 'interval_rate_modifier' );
        $this->dropColumn( 'audio_jobs_shadow', 'interval_rate_modifier' );
	}
}