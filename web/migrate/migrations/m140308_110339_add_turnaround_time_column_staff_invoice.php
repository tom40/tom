<?php

class m140308_110339_add_turnaround_time_column_staff_invoice extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'staff_invoice_records', 'turnaround_time_id', 'INT(11) AFTER service_id' );
    }

    public function safeDown()
    {
        $this->dropColumn( 'staff_invoice_records', 'turnaround_time_id' );
    }
}