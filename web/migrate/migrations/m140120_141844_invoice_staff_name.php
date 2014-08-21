<?php

class m140120_141844_invoice_staff_name extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'staff_invoice', 'staff_name', "VARCHAR(255) AFTER `user_id`" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'staff_invoice', 'staff_name' );
    }
}