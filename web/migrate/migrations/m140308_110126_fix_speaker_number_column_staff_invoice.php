<?php

class m140308_110126_fix_speaker_number_column_staff_invoice extends CDbMigration
{
    public function safeUp()
    {
        $model   = new Application_Model_StaffInvoiceRecordMapper();
        $records = $model->fetchAll( 'service_id > 0' );

        foreach ( $records as $record )
        {
            $record->speaker_numbers_id = $record->getAudioJob()->speaker_numbers_id;
            $record->save();
        }
    }

    public function safeDown()
    {
        return true;
    }
}