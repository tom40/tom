<?php

class m140308_093820_fix_service_column extends CDbMigration
{
	public function safeUp()
	{
        //ini_set( 'memory_limit', '1000M' );
        $model   = new Application_Model_StaffInvoiceRecordMapper();
        $records = $model->fetchAll( 'audio_job_typist_id > 0 AND created_date > DATE_SUB(NOW(), INTERVAL 2 WEEK)' );

        foreach ( $records as $record )
        {
            $serviceId = $record->getAudioJob()->service_id;
            if ( !empty( $serviceId ) )
            {
                $record->service_id            = $serviceId;
                $record->transcription_type_id = NULL;
            }
            $record->save();
        }
	}

	public function safeDown()
	{
        return true;
	}
}