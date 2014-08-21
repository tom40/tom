<?php

class m130813_151055_staff_statuses extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'lkp_audio_job_statuses', 'typist_editable', "enum('0', '1') DEFAULT '0' AFTER `description`" );
        $this->addColumn( 'lkp_audio_job_statuses', 'proofreader_editable', "enum('0', '1') DEFAULT '0' AFTER `description`" );
        $this->dropColumn( 'lkp_audio_job_statuses', 'staff_editable' );
    }

    public function safeDown()
    {
        $this->dropColumn( 'lkp_audio_job_statuses', 'typist_editable' );
        $this->dropColumn( 'lkp_audio_job_statuses', 'proofreader_editable' );
        $this->addColumn( 'lkp_audio_job_statuses', 'staff_editable', "enum('0', '1') DEFAULT '0' AFTER `description`" );
    }
}