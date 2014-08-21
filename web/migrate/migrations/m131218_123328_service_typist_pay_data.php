<?php

class m131218_123328_service_typist_pay_data extends CDbMigration
{
	public function safeUp()
	{

        $data = array(
            '1' => array( 45, 49, 53 ),
            '2' => array( 34, 36, 40 ),
            '3' => array( 28, 30, 34 ),
            '4' => array( 19, 20, 22 ),
            '5' => array( 65, 65, 65 ),
            '6' => array( 65, 65, 65 ),
            '7' => array( 53, 55, 57 ),
            '8' => array( 60, 61, 61 )
        );

        foreach ( $data as $service => $rates )
        {
            $this->update(
                 's_service',
                     array(
                        'typist_grade_1' => $rates[0],
                        'typist_grade_2' => $rates[1],
                        'typist_grade_3' => $rates[2],
                    ),
                     'id = ' . $service
            );
        }

        for ( $c = 1; $c < 5; $c++ )
        {
            $this->update(
                's_service',
                    array(
                        'has_legal'    => '1',
                        'legal_grade_1' => 50,
                        'legal_grade_2' => 50,
                        'legal_grade_3' => 54
                    ),
                    'id = ' . $c
            );
        }
	}

	public function safeDown()
	{
        $this->update(
            's_service',
                array(
                     'typist_grade_1' => 0,
                     'typist_grade_2' => 0,
                     'typist_grade_3' => 0,
                     'legal_grade_1'  => 0,
                     'legal_grade_2'  => 0,
                     'legal_grade_3'  => 0,
                ),
                'id > 0'
        );
	}
}