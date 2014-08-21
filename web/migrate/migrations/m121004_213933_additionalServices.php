<?php

class m121004_213933_additionalServices extends CDbMigration
{
	public function safeUp()
	{
           $this->createTable('additional_services', array(
                'id'          => 'INT(11) NOT NULL AUTO_INCREMENT',
                'name'        => 'VARCHAR(255) NOT NULL',
                'description' => 'TEXT',
                'price'       => 'DECIMAL (8,4)',
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
            $this->dropTable('additional_services');
	}
}