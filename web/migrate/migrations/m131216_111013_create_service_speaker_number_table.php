<?php

class m131216_111013_create_service_speaker_number_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_service_speaker_number` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `service_id` INT(11) NOT NULL ,
              `speaker_number_id` INT(11) NOT NULL ,
              `percentage` DECIMAL(9,4) NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_service_speaker_number_service',
             's_service_speaker_number',
             'service_id',
             's_service',
             'id',
             'NO ACTION',
             'NO ACTION'
        );

        $this->addForeignKey(
             'fk_service_price_modifier_speaker_number',
             's_service_speaker_number',
             'speaker_number_id',
             's_speaker_number',
             'id',
             'NO ACTION',
             'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_service_speaker_number' );
    }
}