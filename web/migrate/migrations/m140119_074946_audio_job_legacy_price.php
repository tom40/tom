<?php

class m140119_074946_audio_job_legacy_price extends CDbMigration
{
	public function safeUp()
	{
        ini_set('memory_limit','755M');
        $createdDate = '2014-01-06 09:00:00';

        $mapper         = new Application_Model_AudioJobMapper();
        $priceMapper    = new Application_Model_TranscriptionPriceMapper();
        $priceMapperOld = new Application_Model_TranscriptionPriceMapperLegacy();

        $old = $mapper->fetchAll( "created_date < '" . $createdDate . "'" );
        $new = $mapper->fetchAll( "created_date >= '" . $createdDate . "'" );

        foreach ( $old as $oldRow )
        {
            $oldRow->rate = $priceMapperOld->getPrice( $oldRow->transcription_type_id, $oldRow->turnaround_time_id );
            $oldRow->save();
        }

        foreach ( $new as $newRow )
        {
            $newRow->rate = $priceMapper->getPrice( $newRow->transcription_type_id, $newRow->turnaround_time_id );
            $newRow->save();
        }

	}

	public function safeDown()
	{
        return true;
	}
}