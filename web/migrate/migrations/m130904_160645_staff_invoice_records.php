<?php

class m130904_160645_staff_invoice_records extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn( 'staff_invoice_records', 'created_date', "DATETIME AFTER `audio_job_typist_id`" );
        $this->addColumn( 'staff_invoice_records', 'total', "DECIMAL(9,2) DEFAULT NULL AFTER `audio_job_typist_id`" );
        $this->addColumn( 'staff_invoice_records', 'minutes_worked', "INT DEFAULT NULL AFTER `audio_job_typist_id`" );
        $this->addColumn( 'staff_invoice_records', 'pay_per_minute', "INT DEFAULT NULL AFTER `audio_job_typist_id`" );
        $this->addColumn( 'staff_invoice_records', 'ad_hoc', "INT DEFAULT NULL AFTER `audio_job_typist_id`" );
        $this->addColumn( 'staff_invoice_records', 'deleted', "ENUM('0','1') DEFAULT '0' AFTER `audio_job_typist_id`" );
        $this->addColumn( 'staff_invoice_records', 'sub_standard', "ENUM('0','1') DEFAULT '0' AFTER `audio_job_typist_id`" );
        $this->addColumn( 'staff_invoice_records', 'name', "VARCHAR(255) DEFAULT NULL AFTER `audio_job_typist_id`" );
        $this->addColumn( 'staff_invoice_records', 'inaccurate', "ENUM('0','1') DEFAULT '0' AFTER `audio_job_typist_id`" );
	}

	public function safeDown()
	{
        $this->dropColumn( 'staff_invoice_records', 'deleted' );
        $this->dropColumn( 'staff_invoice_records', 'minutes_worked' );
        $this->dropColumn( 'staff_invoice_records', 'pay_per_minute' );
        $this->dropColumn( 'staff_invoice_records', 'ad_hoc' );
        $this->dropColumn( 'staff_invoice_records', 'sub_standard' );
        $this->dropColumn( 'staff_invoice_records', 'name' );
        $this->dropColumn( 'staff_invoice_records', 'created_date' );
        $this->dropColumn( 'staff_invoice_records', 'total' );
        $this->dropColumn( 'staff_invoice_records', 'inaccurate' );

	}
}