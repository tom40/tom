<?php

class m131216_111310_create_client_turnaround_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_client_turnaround_time` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `client_id` INT(11) NOT NULL ,
              `turnaround_time_id` INT(11) NOT NULL ,
              `percentage` DECIMAL(9,4) NULL DEFAULT NULL,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_client_service_turnaround_turnaround',
                 's_client_turnaround_time',
                 'turnaround_time_id',
                 's_turnaround_time',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );

        $this->addForeignKey(
             'fk_client_service_turnaround_client',
                 's_client_turnaround_time',
                 'client_id',
                 'clients',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_client_turnaround_time' );
    }
}