<?php

class m121002_235447_add_summaries_to_report extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('report', 'total_score', 'DECIMAL(10,0) DEFAULT NULL');
        $this->addColumn('report', 'comment', 'TEXT DEFAULT NULL');
	}

	public function safeDown()
	{
        $this->dropColumn('report', 'total_score');
        $this->dropColumn('report', 'comment');
	}
}