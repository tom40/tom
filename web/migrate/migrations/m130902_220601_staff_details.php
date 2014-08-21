<?php

class m130902_220601_staff_details extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn( 'users', 'address', "TEXT AFTER `mobile`" );
        $this->addColumn( 'users', 'acc_no', "VARCHAR(55) AFTER `mobile`" );
        $this->addColumn( 'users', 'sort_code', "VARCHAR(55) AFTER `mobile`" );

        $this->addColumn( 'users_shadow', 'address', "TEXT AFTER `mobile`" );
        $this->addColumn( 'users_shadow', 'acc_no', "VARCHAR(55) AFTER `mobile`" );
        $this->addColumn( 'users_shadow', 'sort_code', "VARCHAR(55) AFTER `mobile`" );
	}

	public function safeDown()
	{
        $this->dropColumn( 'users', 'address' );
        $this->dropColumn( 'users', 'acc_no' );
        $this->dropColumn( 'users', 'sort_code' );

        $this->dropColumn( 'users_shadow', 'address' );
        $this->dropColumn( 'users_shadow', 'acc_no' );
        $this->dropColumn( 'users_shadow', 'sort_code' );
	}
}