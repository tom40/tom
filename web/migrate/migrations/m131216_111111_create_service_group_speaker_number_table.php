<?php

class m131216_111111_create_service_group_speaker_number_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_service_group_speaker_number` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `service_group_id` INT(11) NOT NULL ,
              `speaker_number_id` INT(11) NOT NULL ,
              `percentage` DECIMAL(9,4) NULL DEFAULT NULL,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_service_group_speaker_number_group',
             's_service_group_speaker_number',
             'service_group_id',
             's_service_group',
             'id',
             'NO ACTION',
             'NO ACTION'
        );

        $this->addForeignKey(
             'fk_service_group_speaker_number_speaker_number',
             's_service_group_speaker_number',
             'speaker_number_id',
             's_price_modifier',
             'id',
             'NO ACTION',
             'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_service_group_speaker_number' );
    }
}