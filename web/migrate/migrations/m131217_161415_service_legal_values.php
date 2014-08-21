<?php

class m131217_161415_service_legal_values extends CDbMigration
{

	public function safeUp()
	{
        $this->update(
            's_service',
                array( 'legal_modifier' => '30' ),
                'id < 5'
        );
	}

	public function safeDown()
	{
        $this->update(
             's_service',
                 array( 'legal_modifier' => '0' ),
                 'id < 5'
        );
	}
}