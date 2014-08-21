<?php

class m131111_140353_audio_job_delete_shadow extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'audio_jobs_shadow', 'deleted', "DATETIME DEFAULT NULL" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'audio_jobs_shadow', 'deleted' );
    }
}