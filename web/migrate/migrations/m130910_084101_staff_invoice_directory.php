<?php

class m130910_084101_staff_invoice_directory extends CDbMigration
{
	public function up()
	{
        mkdir( APPLICATION_PATH . '/../data/staff_invoice' );
        chmod( APPLICATION_PATH . '/../data/staff_invoice', 0777 );
	}

	public function down()
	{
        return true;
	}
}