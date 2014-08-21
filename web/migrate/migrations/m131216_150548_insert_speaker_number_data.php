<?php

class m131216_150548_insert_speaker_number_data extends CDbMigration
{
	public function safeUp()
	{
        $this->insert(
             's_speaker_number',
                 array(
                      'id'         => 1,
                      'name'       => '1-3',
                 )
        );

        $this->insert(
             's_speaker_number',
                 array(
                      'id'         => 2,
                      'name'       => '4+',
                 )
        );
	}

	public function safeDown()
	{
        $this->execute('SET foreign_key_checks = 0');
        $this->truncateTable( 's_speaker_number' );
        $this->execute('SET foreign_key_checks = 1');
	}
}