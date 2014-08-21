<?php

class m121106_153403_add_shift_id_to_user_jobs extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('audio_jobs_typists', 'shift_id', 'INT(11) AFTER user_id');
        $this->addColumn('audio_jobs_typists_shadow', 'shift_id', 'INT(11) AFTER user_id');
        $this->addColumn('audio_jobs_proofreaders', 'shift_id', 'INT(11) AFTER user_id');
        $this->addColumn('audio_jobs_proofreaders_shadow', 'shift_id', 'INT(11) AFTER user_id');
	}

	public function safeDown()
	{
        $this->dropColumn('audio_jobs_typists', 'shift_id');
        $this->dropColumn('audio_jobs_typists_shadow', 'shift_id');
        $this->dropColumn('audio_jobs_proofreaders', 'shift_id');
        $this->dropColumn('audio_jobs_proofreaders_shadow', 'shift_id');
	}
}