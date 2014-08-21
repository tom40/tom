<?php

class m140215_182323_service_description extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn( 's_service', 'description', 'TEXT AFTER `name`' );
        $this->addColumn( 's_price_modifier', 'description', 'TEXT AFTER `name`' );
	}

	public function safeDown()
	{
        $this->dropColumn( 's_service', 'description' );
        $this->dropColumn( 's_price_modifier', 'description' );
	}
}