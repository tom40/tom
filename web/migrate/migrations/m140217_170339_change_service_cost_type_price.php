<?php

class m140217_170339_change_service_cost_type_price extends CDbMigration
{
    public function safeUp()
    {
        $this->update( 's_service_price_modifier', array( 'modifier_value' => '25' ), 'price_modifier_id=:price_modifier_id', array( ':price_modifier_id' => 5 ) );
    }

    public function safeDown()
    {
        $this->update( 's_service_price_modifier', array( 'modifier_value' => '2.5' ), 'price_modifier_id=:price_modifier_id', array( ':price_modifier_id' => 5 ) );
    }
}