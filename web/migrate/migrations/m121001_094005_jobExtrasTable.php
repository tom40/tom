<?php

class m121001_094005_jobExtrasTable extends CDbMigration
{
	public function safeUp()
	{
        $this->createTable('jobs_extras', array(
                'id'             => 'INT(11) NOT NULL AUTO_INCREMENT',
                'job_id'         => "INT(11) NOT NULL",
                'description'    => "TEXT",
                'price'          => 'DECIMAL(10,4)',
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
        $this->dropTable('jobs_extras');
	}
}