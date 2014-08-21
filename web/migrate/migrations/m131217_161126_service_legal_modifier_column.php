<?php

class m131217_161126_service_legal_modifier_column extends CDbMigration
{

	public function safeUp()
	{
        $this->addColumn( 's_service', 'legal_modifier', 'DECIMAL(9,4) NOT NULL DEFAULT 0' );
	}

	public function safeDown()
	{
        $this->dropColumn( 's_service', 'legal_modifier' );
	}
}