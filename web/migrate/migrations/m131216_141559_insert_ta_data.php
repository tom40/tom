<?php

class m131216_141559_insert_ta_data extends CDbMigration
{
	public function safeUp()
	{
        $this->insert(
             's_turnaround_time',
             array(
                  'id'         => 1,
                  'name'       => '48HR',
                  'sort_order' => 1
             )
        );

        $this->insert(
             's_turnaround_time',
                 array(
                      'id'         => 2,
                      'name'       => '24HR',
                      'sort_order' => 2
                 )
        );

        $this->insert(
             's_turnaround_time',
                 array(
                      'id'         => 3,
                      'name'       => '12HR',
                      'sort_order' => 3
                 )
        );

        $this->insert(
             's_turnaround_time',
                 array(
                      'id'         => 4,
                      'name'       => '7DAY',
                      'sort_order' => 4
                 )
        );
	}

	public function safeDown()
	{
        $this->execute('SET foreign_key_checks = 0');
        $this->truncateTable( 's_turnaround_time' );
        $this->execute('SET foreign_key_checks = 1');
	}
}