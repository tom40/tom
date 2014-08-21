<?php

class m131216_110845_create_price_modifier_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_price_modifier` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `name` VARCHAR(255) NULL ,
              `type` ENUM('c','p') NOT NULL DEFAULT 'p',
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_price_modifier' );
    }
}