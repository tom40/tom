<?php

class m140308_093810_add_service_column extends CDbMigration
{

    public function safeUp()
    {
        $this->addColumn( 'staff_invoice_records', 'service_id', 'INT(11) AFTER transcription_type_id' );
    }

    public function safeDown()
    {
        $this->dropColumn( 'staff_invoice_records', 'service_id' );
    }

}