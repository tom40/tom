<?php

class m140119_061825_sub_standard_invoice extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'staff_invoice_records', 'replacement', "enum('0', '1') DEFAULT '0' AFTER `sub_standard`" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'staff_invoice_records', 'replacement' );
    }
}