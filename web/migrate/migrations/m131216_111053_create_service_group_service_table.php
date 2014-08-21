<?php

class m131216_111053_create_service_group_service_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_service_group_service` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `service_id` INT(11) NOT NULL ,
              `service_group_id` INT(11) NOT NULL ,
              `base_price` DECIMAL(9,4) NULL DEFAULT NULL,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_service_group_service',
             's_service_group_service',
             'service_id',
             's_service',
             'id',
             'NO ACTION',
             'NO ACTION'
        );

        $this->addForeignKey(
             'fk_service_group_group',
             's_service_group_service',
             'service_group_id',
             's_service_group',
             'id',
             'NO ACTION',
             'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_service_group_service' );
    }
}