<?php

class m120928_141538_jobDiscountOverride extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('jobs', 'discount', 'DECIMAL(6,4) DEFAULT NULL');
	}

	public function safeDown()
	{
        $this->dropColumn('jobs', 'discount');
	}
}