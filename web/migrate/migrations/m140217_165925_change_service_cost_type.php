<?php

class m140217_165925_change_service_cost_type extends CDbMigration
{

	public function safeUp()
	{
        $this->update( 's_price_modifier', array( 'type' => 'p' ), 'id=:id', array( ':id' => 5 ) );
	}

	public function safeDown()
	{
        $this->update( 's_price_modifier', array( 'type' => 'c' ), 'id=:id', array( ':id' => 5 ) );
	}
}