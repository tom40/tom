<?php

class m130228_023406_remove_audio_jobs_proofreaders_user_constraints extends CDbMigration
{
    public function safeUp()
	{
        $this->execute("
            set foreign_key_checks=0;
            ALTER TABLE audio_jobs_proofreaders DROP FOREIGN KEY fk_audio_job_proofreaders_user_id_1;
            set foreign_key_checks=1;"
        );
	}

	public function safeDown()
	{
        $this->execute("
            set foreign_key_checks=0;
            ALTER TABLE audio_jobs_proofreaders
            ADD CONSTRAINT fk_audio_job_proofreaders_user_id_1
            FOREIGN KEY (user_id)
            REFERENCES users(id);
            set foreign_key_checks=1;"
       );
	}
}