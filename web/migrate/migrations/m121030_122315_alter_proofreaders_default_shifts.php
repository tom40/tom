<?php

class m121030_122315_alter_proofreaders_default_shifts extends CDbMigration
{
	public function safeUp()
	{
        $this->renameColumn('proofreaders_default_shift', 'day_name', 'start_day');
        $this->renameColumn('proofreaders_default_shift', 'day_number', 'start_day_number');
        $this->addColumn('proofreaders_default_shift', 'end_day_number', 'INT(11) AFTER start_time');
        $this->addColumn('proofreaders_default_shift', 'end_day', 'VARCHAR(255) DEFAULT null AFTER end_day_number');
	}

	public function safeDown()
	{
        $this->renameColumn('proofreaders_default_shift', 'start_day', 'day_name');
        $this->renameColumn('proofreaders_default_shift', 'start_day_number', 'day_number');
        $this->dropColumn('proofreaders_default_shift', 'end_day');
        $this->dropColumn('proofreaders_default_shift', 'end_day_number');
	}
}