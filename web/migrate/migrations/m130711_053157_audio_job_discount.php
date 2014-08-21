<?php

class m130711_053157_audio_job_discount extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'audio_jobs', 'audio_job_discount', "DECIMAL (5,2) DEFAULT '0'" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'audio_jobs', 'audio_job_discount' );
    }
}