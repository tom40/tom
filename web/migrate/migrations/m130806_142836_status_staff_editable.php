<?php

class m130806_142836_status_staff_editable extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'lkp_audio_job_statuses', 'staff_editable', "enum('0', '1') DEFAULT '0' AFTER `description`" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'lkp_audio_job_statuses', 'staff_editable' );
    }
}