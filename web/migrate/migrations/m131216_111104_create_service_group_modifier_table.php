<?php

class m131216_111104_create_service_group_modifier_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_service_group_modifier` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `service_group_id` INT(11) NOT NULL ,
              `price_modifier_id` INT(11) NOT NULL ,
              `modifier_value` DECIMAL(9,4) NULL DEFAULT NULL,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_service_group_modifier_group',
             's_service_group_modifier',
             'service_group_id',
             's_service_group',
             'id',
             'NO ACTION',
             'NO ACTION'
        );

        $this->addForeignKey(
             'fk_service_group_modifier_modifier',
             's_service_group_modifier',
             'price_modifier_id',
             's_price_modifier',
             'id',
             'NO ACTION',
             'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_service_group_modifier' );
    }
}