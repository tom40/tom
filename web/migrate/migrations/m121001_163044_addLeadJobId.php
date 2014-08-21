<?php

class m121001_163044_addLeadJobId extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('audio_jobs', 'lead_id', 'INT(11) DEFAULT NULL AFTER job_id');
        $this->addColumn('audio_jobs_shadow', 'lead_id', 'INT(11) DEFAULT NULL AFTER job_id');
	}

	public function safeDown()
	{
        $this->dropColumn('audio_jobs', 'lead_id');
        $this->dropColumn('audio_jobs_shadow', 'lead_id');
	}
}