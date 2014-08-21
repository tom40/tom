<?php

class m121004_125437_turnAroundTypesDefaults extends CDbMigration
{
	public function safeUp()
	{
        $mapper = array(
            '1'  => '1',
            '2'  => '2',
            '3'  => '3',
            '4'  => '4',
            '5'  => '5',
            '6'  => '6',
            '7'  => '1',
            '8'  => '2',
            '9'  => '3',
            '10' => '4',
            '11' => '5',
            '12' => '6',
            '13' => '1',
        );
        foreach ($mapper as $key => $value)
        {
            $this->update(
                'lkp_transcription_types',
                array(
                    'turnaround_id' => $value
                ),
                'id = :id',
                array('id' => $key)
            );
        }
	}

	public function safeDown()
	{
        return true;
	}
}