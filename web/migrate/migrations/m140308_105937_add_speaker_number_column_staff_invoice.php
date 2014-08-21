<?php

class m140308_105937_add_speaker_number_column_staff_invoice extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'staff_invoice_records', 'speaker_numbers_id', 'INT(11) AFTER service_id' );
    }

    public function safeDown()
    {
        $this->dropColumn( 'staff_invoice_records', 'speaker_numbers_id' );
    }
}