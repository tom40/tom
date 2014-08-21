<?php

class m121017_155513_proofreaders_shifts extends CDbMigration
{
    public function safeUp()
	{
           $this->createTable('proofreaders_shifts', array(
                'id'         => 'INT(11) NOT NULL AUTO_INCREMENT',
                'shift_id'   => 'INT(11)',
                'shift_date' => 'DATE NOT NULL',
                'user_id'    => 'INT(11) NOT NULL',
                'status'     => "ENUM('BOOKED','HOLIDAY') NOT NULL",
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
            $this->dropTable('proofreaders_shifts');
	}
}