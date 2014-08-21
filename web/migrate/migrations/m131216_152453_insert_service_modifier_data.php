<?php

class m131216_152453_insert_service_modifier_data extends CDbMigration
{
    public function safeUp()
    {
        $this->insert(
             's_service_price_modifier',
                 array(
                      'service_id'        => 1,
                      'price_modifier_id' => 1,
                      'modifier_value'    => 25
                 )
        );

        $this->insert(
             's_service_price_modifier',
                 array(
                      'service_id'        => 1,
                      'price_modifier_id' => 2,
                      'modifier_value'    => 25
                 )
        );

        $this->insert(
             's_service_price_modifier',
                 array(
                      'service_id'        => 1,
                      'price_modifier_id' => 3,
                      'modifier_value'    => 25
                 )
        );

        $this->insert(
             's_service_price_modifier',
                 array(
                      'service_id'        => 1,
                      'price_modifier_id' => 4,
                      'modifier_value'    => 25
                 )
        );

        $this->insert(
             's_service_price_modifier',
                 array(
                      'service_id'        => 2,
                      'price_modifier_id' => 2,
                      'modifier_value'    => 25
                 )
        );

        $this->insert(
             's_service_price_modifier',
                 array(
                      'service_id'        => 2,
                      'price_modifier_id' => 3,
                      'modifier_value'    => 25
                 )
        );

        $this->insert(
             's_service_price_modifier',
                 array(
                      'service_id'        => 3,
                      'price_modifier_id' => 2,
                      'modifier_value'    => 25
                 )
        );

        $this->insert(
             's_service_price_modifier',
                 array(
                      'service_id'        => 3,
                      'price_modifier_id' => 3,
                      'modifier_value'    => 25
                 )
        );

        $this->insert(
             's_service_price_modifier',
                 array(
                      'service_id'        => 4,
                      'price_modifier_id' => 2,
                      'modifier_value'    => 25
                 )
        );

        $this->insert(
             's_service_price_modifier',
                 array(
                      'service_id'        => 4,
                      'price_modifier_id' => 3,
                      'modifier_value'    => 25
                 )
        );

        for( $c = 1; $c < 9; $c++ )
        {
            $this->insert(
                 's_service_price_modifier',
                     array(
                          'service_id'        => $c,
                          'price_modifier_id' => 5,
                          'modifier_value'    => 2.5
                     )
            );
        }
    }

    public function safeDown()
    {
        $this->truncateTable( 's_service_price_modifier' );
    }
}