<?php

class m130912_082153_utr_number extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'users', 'utr_no', "VARCHAR(255) AFTER `acc_no`" );
        $this->addColumn( 'users_shadow', 'utr_no', "VARCHAR(255) AFTER `acc_no`" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'users', 'utr_no' );
        $this->dropColumn( 'users_shadow', 'utr_no' );
    }
}