<?php

class m131216_142336_insert_modifier_data extends CDbMigration
{
    public function safeUp()
    {
        $this->insert(
             's_price_modifier',
                 array(
                      'id'         => 1,
                      'name'       => 'Verbatim',
                      'type'       => 'p'
                 )
        );

        $this->insert(
             's_price_modifier',
                 array(
                      'id'         => 2,
                      'name'       => 'Speaker ID',
                      'type'       => 'p'
                 )
        );

        $this->insert(
             's_price_modifier',
                 array(
                      'id'         => 3,
                      'name'       => 'Medical',
                      'type'       => 'p'
                 )
        );

        $this->insert(
             's_price_modifier',
                 array(
                      'id'         => 4,
                      'name'       => 'Time Codes',
                      'type'       => 'p'
                 )
        );

        $this->insert(
             's_price_modifier',
                 array(
                      'id'         => 5,
                      'name'       => 'Recorded Tel',
                      'type'       => 'c'
                 )
        );
    }

    public function safeDown()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->truncateTable( 's_price_modifier' );
        $this->execute('SET foreign_key_checks = 1');
    }
}