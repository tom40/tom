<?php

class m120928_141232_customPricing extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('clients', 'custom_pricing', 'enum("0","1") DEFAULT "0"');
        $this->addColumn('clients_shadow', 'custom_pricing', 'enum("0","1") DEFAULT "0"');
	}

	public function safeDown()
	{
        $this->dropColumn('clients', 'custom_pricing');
        $this->dropColumn('clients_shadow', 'custom_pricing');
	}
}