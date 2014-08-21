<?php

class m120928_140836_clientDiscount extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('clients', 'discount', 'DECIMAL(6,4) DEFAULT "0.0000"');
        $this->addColumn('clients_shadow', 'discount', 'DECIMAL(6,4) DEFAULT "0.0000"');
	}

	public function safeDown()
	{
        $this->dropColumn('clients', 'discount');
        $this->dropColumn('clients_shadow', 'discount');
	}
}