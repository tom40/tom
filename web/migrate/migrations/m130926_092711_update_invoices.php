<?php

class m130926_092711_update_invoices extends CDbMigration
{
    public function safeUp()
    {
        ini_set('memory_limit','500M');
        $recordMapper = new Application_Model_StaffInvoiceRecordMapper();
        $records      = $recordMapper->fetchAll();

        foreach ( $records as $record )
        {
            $record->updateFromAudioJob();
        }
    }

    public function safeDown()
    {
        return true;
    }
}