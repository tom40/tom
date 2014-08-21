<?php

class m121001_100556_ServiceClientTable extends CDbMigration
{
	public function safeUp()
	{
        $this->createTable('clients_transcription_types', array(
                'id'                  => 'INT(11) NOT NULL AUTO_INCREMENT',
                'client_id'           => "INT(11) NOT NULL",
                'name'                => "VARCHAR(50) NOT NULL",
                'description'         => "TEXT",
                'sort_order'          => 'INT(11)',
                'default_turn_around' => 'INT(11)',
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
        $this->dropTable('clients_transcription_types');
	}
}