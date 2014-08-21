<?php

class m121002_140936_create_report_data extends CDbMigration
{
	public function safeUp()
	{
           $this->createTable('report_data', array(
                'id'          => 'INT(11) NOT NULL AUTO_INCREMENT',
                'report_id'   => 'INT(11) NOT NULL',
                'criteria_id' => 'INT(11) NOT NULL',
                'score'       => 'DECIMAL(10,2) NOT NULL',
                'comment'     => 'TEXT',
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
            $this->dropTable('report_data');
	}
}