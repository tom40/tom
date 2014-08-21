<?php

class m130129_152126_add_complete_to_report extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('report', 'complete', 'TINYINT(1) DEFAULT 0 AFTER audio_job_id');
	}

	public function safeDown()
	{
        $this->dropColumn('report', 'complete');
	}
}