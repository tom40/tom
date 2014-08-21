<?php

class m140211_120917_audio_job_service_id extends CDbMigration
{

	public function safeUp()
	{
        $this->alterColumn( 'audio_jobs', 'transcription_type_id', 'INT(11) NULL' );
        $this->alterColumn( 'audio_jobs_shadow', 'transcription_type_id', 'INT(11) NULL' );

        $this->addColumn( 'audio_jobs', 'service_id', 'INT(11) NULL AFTER transcription_type_id' );
        $this->addColumn( 'audio_jobs_shadow', 'service_id', 'INT(11) NULL AFTER transcription_type_id' );

        $this->alterColumn( 'jobs', 'transcription_type_id', 'INT(11) NULL' );
        $this->alterColumn( 'jobs_shadow', 'transcription_type_id', 'INT(11) NULL' );

        $this->addColumn( 'jobs', 'service_id', 'INT(11) NULL AFTER transcription_type_id' );
        $this->addColumn( 'jobs_shadow', 'service_id', 'INT(11) NULL AFTER transcription_type_id' );
	}

	public function safeDown()
	{
        $this->alterColumn( 'audio_jobs', 'transcription_type_id', 'INT(11) NOT NULL' );
        $this->alterColumn( 'audio_jobs_shadow', 'transcription_type_id', 'INT(11) NOT NULL' );

        $this->dropColumn( 'audio_jobs', 'service_id' );
        $this->dropColumn( 'audio_jobs_shadow', 'service_id' );

        $this->alterColumn( 'jobs', 'transcription_type_id', 'INT(11) NOT NULL' );
        $this->alterColumn( 'jobs_shadow', 'transcription_type_id', 'INT(11) NOT NULL' );

        $this->dropColumn( 'jobs', 'service_id' );
        $this->dropColumn( 'jobs_shadow', 'service_id' );
	}

}