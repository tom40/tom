<?php

class m121004_142003_discountDecimalPlace extends CDbMigration
{

	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->alterColumn('clients', 'discount', 'DECIMAL(5,2) DEFAULT "0.00"');
        $this->alterColumn('jobs', 'discount', 'DECIMAL(5,2) DEFAULT "0.00"');
	}

	public function safeDown()
	{
        $this->alterColumn('clients', 'discount', 'DECIMAL(7,4) DEFAULT "0.00"');
        $this->alterColumn('jobs', 'discount', 'DECIMAL(7,4) DEFAULT "0.00"');
	}
}