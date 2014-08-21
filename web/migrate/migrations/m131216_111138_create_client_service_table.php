<?php

class m131216_111138_create_client_service_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_client_service` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `client_id` INT(11) NOT NULL ,
              `service_id` INT(11) NOT NULL ,
              `base_price` DECIMAL(9,4) NULL DEFAULT NULL,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_client_service_service',
                 's_client_service',
                 'service_id',
                 's_service',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );

        $this->addForeignKey(
             'fk_client_service_client',
                 's_client_service',
                 'client_id',
                 'clients',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_client_service' );
    }
}