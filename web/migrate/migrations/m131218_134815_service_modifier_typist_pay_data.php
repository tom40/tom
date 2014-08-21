<?php

class m131218_134815_service_modifier_typist_pay_data extends CDbMigration
{
	public function safeUp()
	{
        $this->update(
             's_service_price_modifier',
                 array(
                      'typist_percentage' => 10
                 ),
                 'service_id = 1'
        );

        $this->update(
             's_service_price_modifier',
                 array(
                      'typist_percentage' => 10
                 ),
                 'service_id IN(2,3,4) AND price_modifier_id IN(2,3)'
        );

        $speakerData = array(
            1 => 12.25,
            2 => 19.45,
            3 => 16.67,
            4 => 10,
            7 => 10.53,
            8 => 10.53
        );

        foreach ( $speakerData as $key => $data )
        {
            $this->update(
                 's_service_speaker_number',
                     array(
                          'typist_percentage' => $data
                     ),
                     'speaker_number_id = 2 AND service_id = ' . $key
            );
        }
	}

	public function safeDown()
	{
        $this->update(
             's_service_price_modifier',
                 array(
                      'typist_percentage' => 0
                 ),
                 'id > 0'
        );

        $this->update(
             's_service_speaker_number',
                 array(
                      'typist_percentage' => 0
                 ),
                 'id > 0'
        );
	}
}