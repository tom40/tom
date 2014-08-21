<?php

class m131216_110813_create_service_table extends CDbMigration
{

    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_service` (
             `id` INT(11) NOT NULL AUTO_INCREMENT ,
             `name` VARCHAR(255) NOT NULL ,
             `base_price` DECIMAL(9,4) NOT NULL,
             `training_code` VARCHAR(5) NOT NULL ,
             PRIMARY KEY (`id`) )
             ENGINE = InnoDB"
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_service' );
    }

}