<?php

class m121001_092909_serviceToTa extends CDbMigration
{
	public function safeUp()
	{
        $this->createTable('lkp_transcription_turnaround', array(
                'id'                    => 'INT(11) NOT NULL AUTO_INCREMENT',
                'transcription_type_id' => "INT(11) NOT NULL",
                'turnaround_time_id'    => "INT(11)",
                'price'                 => 'DECIMAL(8,4)',
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');
	}

	public function safeDown()
	{
        $this->dropTable('lkp_transcription_turnaround');
	}
}