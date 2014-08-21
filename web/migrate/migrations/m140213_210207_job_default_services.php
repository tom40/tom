<?php

class m140213_210207_job_default_services extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn( 'jobs', 'modifiers', 'TEXT AFTER service_id' );
        $this->addColumn( 'jobs_shadow', 'modifiers', 'TEXT AFTER service_id' );
	}

	public function safeDown()
	{
        $this->dropColumn( 'jobs', 'modifiers' );
        $this->dropColumn( 'jobs_shadow', 'modifiers' );
	}
}