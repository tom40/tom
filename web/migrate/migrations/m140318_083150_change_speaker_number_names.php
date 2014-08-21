<?php

class m140318_083150_change_speaker_number_names extends CDbMigration
{
	public function safeUp()
	{
        $this->update(
             's_speaker_number',
            array(
                'name' => '3 or less',
            ),
            'id = :id',
            array( ':id' => 1 )
        );
        $this->update(
             's_speaker_number',
                 array(
                     'name' => '4 or more',
                 ),
                 'id = :id',
                 array( ':id' => 2 )
        );
	}

	public function safeDown()
	{
        $this->update(
             's_speaker_number',
                 array(
                     'name' => '1-3',
                 ),
                 'id = :id',
                 array( ':id' => 1 )
        );
        $this->update(
             's_speaker_number',
                 array(
                     'name' => '4+',
                 ),
                 'id = :id',
                 array( ':id' => 2 )
        );
	}
}