<?php

class m131216_110941_create_service_turnaround_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_service_turnaround` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `service_id` INT(11) NOT NULL ,
              `turnaround_time_id` INT(11) NOT NULL ,
              `percentage` DECIMAL(9,4) NOT NULL,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
            'fk_service_turnaround_service',
            's_service_turnaround',
            'service_id',
            's_service',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
         'fk_service_turnaround_turnaround_time',
             's_service_turnaround',
             'turnaround_time_id',
             's_turnaround_time',
             'id',
             'NO ACTION',
             'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_service_turnaround' );
    }
}