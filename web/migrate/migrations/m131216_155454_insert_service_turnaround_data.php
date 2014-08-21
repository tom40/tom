<?php

class m131216_155454_insert_service_turnaround_data extends CDbMigration
{

	public function safeUp()
	{
        // 48hr
        for ( $c = 1; $c < 9; $c++ )
        {
            $this->insert(
                's_service_turnaround',
                    array(
                        'service_id'         => $c,
                        'turnaround_time_id' => 1,
                        'percentage'         => 0
                    )
            );
        }

        // 24hr
        $day = array( 20, 20, 20, 20, 20.55, 20, 15, 15 );

        foreach ( $day as $key => $percentage )
        {
            $this->insert(
                 's_service_turnaround',
                     array(
                          'service_id'         => $key + 1,
                          'turnaround_time_id' => 2,
                          'percentage'         => $percentage
                     )
            );
        }

        // 7 Day
        $week = array(
            1 => -7.75,
            2 => -7.75,
            3 => -9.52,
            5 => -23.29,
            6 => -24,
            7 => -15,
            8 => -15
        );

        foreach ( $week as $key => $percentage )
        {
            $this->insert(
                 's_service_turnaround',
                     array(
                          'service_id'         => $key,
                          'turnaround_time_id' => 3,
                          'percentage'         => $percentage
                     )
            );
        }

        // 12HR
        $halfDay = array(
            1 => 35,
            2 => 35,
            3 => 35,
            4 => 35,
            7 => 90,
            8 => 84
        );

        foreach ( $halfDay as $key => $percentage )
        {
            $this->insert(
                 's_service_turnaround',
                     array(
                          'service_id'         => $key,
                          'turnaround_time_id' => 4,
                          'percentage'         => $percentage
                     )
            );
        }
	}

	public function safeDown()
	{
        $this->truncateTable( 's_service_turnaround' );
	}
}