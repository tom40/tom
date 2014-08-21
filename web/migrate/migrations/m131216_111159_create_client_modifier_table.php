<?php

class m131216_111159_create_client_modifier_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_client_modifier` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `client_id` INT(11) NOT NULL ,
              `price_modifier_id` INT(11) NOT NULL ,
              `modifier_value` DECIMAL(9,4) NULL DEFAULT NULL,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_client_service_modifier_modifier',
                 's_client_modifier',
                 'price_modifier_id',
                 's_price_modifier',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );

        $this->addForeignKey(
             'fk_client_service_modifier_client',
                 's_client_modifier',
                 'client_id',
                 'clients',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_client_modifier' );
    }
}