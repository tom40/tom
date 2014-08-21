<?php

class m130731_120218_client_comments extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'clients', 'comments', "TEXT AFTER `postcode`" );
        $this->addColumn( 'clients_shadow', 'comments', 'TEXT' );
    }

    public function safeDown()
    {
        $this->dropColumn( 'clients', 'comments' );
        $this->dropColumn( 'clients_shadow', 'comments' );
    }
}