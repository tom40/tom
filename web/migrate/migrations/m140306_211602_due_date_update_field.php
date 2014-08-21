<?php

class m140306_211602_due_date_update_field extends CDbMigration
{

	public function safeUp()
	{
        $this->addColumn( 'audio_jobs', 'manual_client_due_date', "enum('0','1') AFTER client_due_date" );
        $this->addColumn( 'audio_jobs_shadow', 'manual_client_due_date', "enum('0','1') AFTER client_due_date" );
	}

	public function safeDown()
	{
        $this->dropColumn( 'audio_jobs', 'manual_client_due_date' );
        $this->dropColumn( 'audio_jobs_shadow', 'manual_client_due_date' );
	}

}