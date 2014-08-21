<?php

class m130926_092418_invoice_job_status extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'staff_invoice_records', 'audio_job_status_id', "INT AFTER `audio_job_typist_id`" );
        $this->addColumn( 'staff_invoice_records', 'transcription_type_id', "INT AFTER `audio_job_status_id`" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'staff_invoice_records', 'audio_job_status_id' );
        $this->dropColumn( 'staff_invoice_records', 'transcription_type_id' );
    }
}