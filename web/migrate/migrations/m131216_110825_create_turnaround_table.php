<?php

class m131216_110825_create_turnaround_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_turnaround_time` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `name` VARCHAR(255) NULL ,
              `sort_order` INT NULL ,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_turnaround_time' );
    }
}