<?php

class m131218_121752_service_typist_modifiers extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn( 's_service_price_modifier', 'typist_percentage', 'DECIMAL(9,4) NOT NULL DEFAULT 0' );
        $this->addColumn( 's_service_speaker_number', 'typist_percentage', 'DECIMAL(9,4) NOT NULL DEFAULT 0' );
	}

	public function safeDown()
	{
        $this->dropColumn( 's_service_price_modifier', 'typist_percentage' );
        $this->dropColumn( 's_service_speaker_number', 'typist_percentage' );
	}
}