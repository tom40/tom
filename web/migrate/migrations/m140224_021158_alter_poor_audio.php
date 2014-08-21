<?php

class m140224_021158_alter_poor_audio extends CDbMigration
{

	public function safeUp()
	{
        $this->alterColumn( 'audio_jobs', 'poor_audio', 'DECIMAL(3,2) DEFAULT NULL' );
        $this->alterColumn( 'audio_jobs_shadow', 'poor_audio', 'DECIMAL(3,2) DEFAULT NULL' );

        $this->update( 'audio_jobs', array( 'poor_audio' => '0.00' ), 'id > :id', array( ':id' => 0 ) );
        $this->update( 'audio_jobs_shadow', array( 'poor_audio' => '0.00' ), 'id > :id', array( ':id' => 0 ) );
	}

	public function safeDown()
	{
        $this->alterColumn( 'audio_jobs', 'poor_audio', "ENUM('0','1') DEFAULT '0'" );
        $this->alterColumn( 'audio_jobs_shadow', 'poor_audio', "ENUM('0','1') DEFAULT '0'" );

        $this->update( 'audio_jobs', array( 'poor_audio' => '0' ), 'id > :id', array( ':id' => 0 ) );
        $this->update( 'audio_jobs_shadow', array( 'poor_audio' => '0' ), 'id > :id', array( ':id' => 0 ) );
	}
}