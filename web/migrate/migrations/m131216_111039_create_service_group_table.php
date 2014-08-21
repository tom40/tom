<?php

class m131216_111039_create_service_group_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_service_group` (
             `id` INT(11) NOT NULL AUTO_INCREMENT ,
             `name` VARCHAR(255) NULL ,
             PRIMARY KEY (`id`) )
             ENGINE = InnoDB"
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_service_group' );
    }
}