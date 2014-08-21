<?php

class m131216_110856_create_speaker_number_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_speaker_number` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `name` VARCHAR(255) NULL ,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_speaker_number' );
    }
}