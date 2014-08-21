<?php

class m140220_144912_audio_job_status_returned extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn( 'lkp_audio_job_statuses', 'returned', "ENUM('0','1') DEFAULT '0' AFTER `complete`" );
        $this->update(
            'lkp_audio_job_statuses',
            array( 'returned' => '1' ),
            'id = :id',
            array( ':id' => 17 )
        );
	}

	public function safeDown()
	{
        $this->dropColumn( 'lkp_audio_job_statuses', 'returned' );
	}
}