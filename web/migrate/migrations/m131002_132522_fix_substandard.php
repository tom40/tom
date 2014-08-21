<?php

class m131002_132522_fix_substandard extends CDbMigration
{
    public function safeUp()
    {
        ini_set('memory_limit','800M');
        $recordMapper = new Application_Model_StaffInvoiceRecordMapper();
        $records      = $recordMapper->fetchAll();

        foreach ( $records as $record )
        {
            if ( '0' == $record->ad_hoc )
            {
                if ('1' == $record->getAudioJobTypist()->substandard_payrate )
                {
                    $record->sub_standard = 1;
                    $record->save();
                }
            }
        }
    }

    public function safeDown()
    {
        return true;
    }
}