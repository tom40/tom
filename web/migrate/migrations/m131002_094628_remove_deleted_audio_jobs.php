<?php

class m131002_094628_remove_deleted_audio_jobs extends CDbMigration
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
                if ('1' == $record->getAudioJob()->archived )
                {
                    $record->delete();
                }
                elseif ( '1' != $record->getAudioJobTypist()->current && '1' && '1' != $record->getAudioJobTypist()->substandard_payrate )
                {
                    $record->delete();
                }
            }
        }
    }

    public function safeDown()
    {
        return true;
    }
}