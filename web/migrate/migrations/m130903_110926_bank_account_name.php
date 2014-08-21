<?php

class m130903_110926_bank_account_name extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'users', 'acc_name', "VARCHAR(255) AFTER `acc_no`" );
        $this->addColumn( 'users_shadow', 'acc_name', "VARCHAR(255) AFTER `acc_no`" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'users', 'acc_name' );
        $this->dropColumn( 'users_shadow', 'acc_name' );
    }
}