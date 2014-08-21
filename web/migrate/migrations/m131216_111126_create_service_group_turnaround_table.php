<?php

class m131216_111126_create_service_group_turnaround_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute(
             "CREATE  TABLE `s_service_group_turnaround_time` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `service_group_id` INT(11) NOT NULL ,
              `turnaround_time_id` INT(11) NOT NULL ,
              `percentage` DECIMAL(9,4) NULL DEFAULT NULL,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_service_group_turnaround_time_group',
                 's_service_group_turnaround_time',
                 'service_group_id',
                 's_service_group',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );

        $this->addForeignKey(
             'fk_service_group_turnaround_time_turnaround',
                 's_service_group_turnaround_time',
                 'turnaround_time_id',
                 's_turnaround_time',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );
    }

    public function safeDown()
    {
        $this->dropTable( 's_service_group_turnaround_time' );
    }
}