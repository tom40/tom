<?php

class m140308_093803_staff_invoice_modifiers extends CDbMigration
{

	public function safeUp()
	{
        $this->addColumn( 'staff_invoice_records', 'price_modifiers', 'TEXT AFTER transcription_type_id' );
	}

	public function safeDown()
	{
        $this->dropColumn( 'staff_invoice_records', 'price_modifiers' );
	}

}