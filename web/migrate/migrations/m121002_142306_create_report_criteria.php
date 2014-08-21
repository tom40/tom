<?php

class m121002_142306_create_report_criteria extends CDbMigration
{
	public function safeUp()
	{
           $this->createTable('report_criteria', array(
                'id'        => 'INT(11) NOT NULL AUTO_INCREMENT',
                'area'      => 'VARCHAR(255) NOT NULL',
                'score'     => 'DECIMAL(10,2) NOT NULL',
                'criteria'  => 'TEXT',
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
            $this->dropTable('report_criteria');
	}
}