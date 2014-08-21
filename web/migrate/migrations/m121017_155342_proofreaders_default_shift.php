<?php

class m121017_155342_proofreaders_default_shift extends CDbMigration
{
    public function safeUp()
	{
           $this->createTable('proofreaders_default_shift', array(
                'id'          => 'INT(11) NOT NULL AUTO_INCREMENT',
                'day_number'  => 'INT(11) NOT NULL',
                'day_name'    => 'VARCHAR(255) NOT NULL',
                'start_time'  => 'TIME NOT NULL',
                'end_time'    => 'TIME NOT NULL',
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
            $this->dropTable('proofreaders_default_shift');
	}
}