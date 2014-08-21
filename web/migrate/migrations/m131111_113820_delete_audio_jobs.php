<?php

class m131111_113820_delete_audio_jobs extends CDbMigration
{

	public function safeUp()
	{
        $this->addColumn( 'audio_jobs', 'deleted', "DATETIME DEFAULT NULL" );
	}

	public function safeDown()
	{
        $this->dropColumn( 'audio_jobs', 'deleted' );
	}
}