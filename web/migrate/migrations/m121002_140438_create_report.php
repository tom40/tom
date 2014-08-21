<?php

class m121002_140438_create_report extends CDbMigration
{
    public function safeUp()
	{
           $this->createTable('report', array(
                'id'              => 'INT(11) NOT NULL AUTO_INCREMENT',
                'typist_id'       => 'INT(11) NOT NULL',
                'audio_job_id'    => 'INT(11) NOT NULL',
                'date_created'    => 'DATETIME',
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
            $this->dropTable('report');
	}
}