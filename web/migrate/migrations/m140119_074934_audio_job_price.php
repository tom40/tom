<?php

class m140119_074934_audio_job_price extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'audio_jobs', 'rate', "DECIMAL(8,4) AFTER `transcription_type_id`" );
        $this->addColumn( 'audio_jobs_shadow', 'rate', "DECIMAL(8,4)" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'audio_jobs', 'rate' );
        $this->dropColumn( 'audio_jobs_shadow', 'rate' );
    }
}