<?php

class m131216_111151_create_client_service_group_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_client_service_group` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `service_group_id` INT(11) NOT NULL ,
              `client_id` INT(11) NOT NULL ,
              `base_price` DECIMAL(9,4) NULL DEFAULT NULL,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_client_service_group_group',
                 's_client_service_group',
                 'service_group_id',
                 's_service_group',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );

        $this->addForeignKey(
             'fk_client_service_group_client',
                 's_client_service_group',
                 'client_id',
                 'clients',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_client_service_group' );
    }
}