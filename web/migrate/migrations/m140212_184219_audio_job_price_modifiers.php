<?php

class m140212_184219_audio_job_price_modifiers extends CDbMigration
{
	public function safeUp()
	{
        $this->execute(
             "CREATE  TABLE `audio_jobs_price_modifiers` (
              `id` INT(11) NOT NULL AUTO_INCREMENT ,
              `audio_job_id` INT(11) NOT NULL ,
              `service_price_modifier_id` INT(11) NOT NULL ,
              PRIMARY KEY (`id`) )
              ENGINE = InnoDB"
        );

        $this->addForeignKey(
             'fk_audio_job',
                 'audio_jobs_price_modifiers',
                 'audio_job_id',
                 'audio_jobs',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );

        $this->addForeignKey(
             'fk_service_price_modifier',
                 'audio_jobs_price_modifiers',
                 'service_price_modifier_id',
                 's_service_price_modifier',
                 'id',
                 'NO ACTION',
                 'NO ACTION'
        );
	}

	public function safeDown()
	{
        $this->dropTable( 'audio_jobs_price_modifiers' );
	}
}