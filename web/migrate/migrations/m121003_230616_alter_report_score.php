<?php

class m121003_230616_alter_report_score extends CDbMigration
{
	public function safeUp()
	{
        $this->alterColumn('report', 'total_score', 'DECIMAL(10,2) DEFAULT NULL');
	}

	public function safeDown()
	{
        $this->alterColumn('report', 'total_score', 'DECIMAL(10,0) DEFAULT NULL');
	}
}