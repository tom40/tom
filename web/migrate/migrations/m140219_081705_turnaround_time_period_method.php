<?php

class m140219_081705_turnaround_time_period_method extends CDbMigration
{

	public function safeUp()
	{
        $this->addColumn( 's_turnaround_time', 'period_method', 'VARCHAR(20) NOT NULL' );

        $data = array(
            '48HR' => 'TwoDays',
            '24HR' => 'Day',
            '12HR' => 'HalfDay',
            '7DAY' => 'Week'
        );

        foreach ( $data as $key => $value )
        {
            $this->update(
                's_turnaround_time',
                array( 'period_method' => $value ),
                'name = :name',
                array( ':name' => $key )
            );
        }
	}

	public function safeDown()
	{
        $this->dropColumn( 's_turnarond_time', 'period_method' );
	}

}