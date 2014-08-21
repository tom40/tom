<?php

class m130226_173058_remove_audio_jobs_typists_user_constraint extends CDbMigration
{
    public function safeUp()
	{
        $this->execute("
            set foreign_key_checks=0;
            ALTER TABLE audio_jobs_typists DROP FOREIGN KEY fk_audio_job_typists_user_id_1;
            set foreign_key_checks=1;"
        );
	}

	public function safeDown()
	{
        $this->execute("
            set foreign_key_checks=0;
            ALTER TABLE audio_jobs_typists
            ADD CONSTRAINT fk_audio_job_typists_user_id_1
            FOREIGN KEY (user_id)
            REFERENCES users(id);
            set foreign_key_checks=1;"
       );
	}
}