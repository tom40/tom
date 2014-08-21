<?php

class m121001_232745_alter_lkp_transcription_type extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('lkp_transcription_types', 'turnaround_id', 'INT DEFAULT NULL');
	}

	public function safeDown()
	{
        $this->dropColumn('lkp_transcription_types', 'turnaround_id');
	}
}