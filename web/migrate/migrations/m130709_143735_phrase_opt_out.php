<?php

class m130709_143735_phrase_opt_out extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'users', 'phrase_opt_in', "enum('0','1') DEFAULT '0'" );
        $this->addColumn( 'users_shadow', 'phrase_opt_in', "enum('0','1') DEFAULT '0'" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'users', 'phrase_opt_in' );
        $this->dropColumn( 'users_shadow', 'phrase_opt_in' );
    }
}