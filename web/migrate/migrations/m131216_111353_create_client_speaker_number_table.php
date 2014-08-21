<?php

class m131216_111353_create_client_speaker_number_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_client_speaker_number` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `client_id` INT(11) NOT NULL ,
              `speaker_number_id` INT(11) NOT NULL ,
              `percentage` DECIMAL(9,4) NULL DEFAULT NULL,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_client_speaker_number',
                 's_client_speaker_number',
                 'speaker_number_id',
                 's_speaker_number',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );

        $this->addForeignKey(
             'fk_client_speaker_number_client',
                 's_client_speaker_number',
                 'client_id',
                 'clients',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_client_speaker_number' );
    }
}