<?php

class m130715_133222_audio_job_discount_shadow extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'audio_jobs_shadow', 'audio_job_discount', "DECIMAL (5,2) DEFAULT '0'" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'audio_jobs_shadow', 'audio_job_discount' );
    }
}