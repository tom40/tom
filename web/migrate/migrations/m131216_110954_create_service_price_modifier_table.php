<?php

class m131216_110954_create_service_price_modifier_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_service_price_modifier` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `service_id` INT(11) NOT NULL ,
              `price_modifier_id` INT(11) NOT NULL ,
              `modifier_value` DECIMAL(9,4) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_service_price_modifier_service',
             's_service_price_modifier',
             'service_id',
             's_service',
             'id',
             'NO ACTION',
             'NO ACTION'
        );

        $this->addForeignKey(
             'fk_service_price_modifier_modifier',
             's_service_price_modifier',
             'price_modifier_id',
             's_price_modifier',
             'id',
             'NO ACTION',
             'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_service_price_modifier' );
    }
}