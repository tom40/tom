<?php

class m140308_110349_fix_turnaround_time_column_staff_invoice extends CDbMigration
{
    public function safeUp()
    {
        $model   = new Application_Model_StaffInvoiceRecordMapper();
        $records = $model->fetchAll( 'service_id > 0' );

        foreach ( $records as $record )
        {
            $record->turnaround_time_id = $record->getAudioJob()->turnaround_time_id;
            $record->save();
        }
    }

    public function safeDown()
    {
        return true;
    }
}