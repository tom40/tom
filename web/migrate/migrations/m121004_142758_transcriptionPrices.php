<?php

class m121004_142758_transcriptionPrices extends CDbMigration
{
	public function safeUp()
	{
        $mapper = array(
            '1'  => array(
                '1' => '22.5000',
                '2' => '16.8750',
                '3' => '18.1250',
                '4' => '19.3750',
                '5' => '22.5000',
            ),
            '2'  => array(
                '1' => '14.3750',
                '2' => '15.6250',
                '3' => '16.8750',
                '4' => '18.1250',
                '5' => '21.2500'
            ),
            '3'  => array(
                '3' => '13.7500',
                '4' => '15.7500',
                '5' => '17.5000',
                '6' => '12.5000'
            ),
            '4'  => array(
                '3' => '10.0000',
                '4' => '12.5000',
                '5' => '15.0000',
            ),
            '5'  => array(
                '1' => '15.6250',
                '2' => '16.8750',
                '3' => '18.1250',
                '4' => '19.3750',
                '5' => '22.5000',
            ),
            '6'  => array(
                '1' => '15.6250',
                '2' => '16.8750',
                '3' => '18.1250',
                '4' => '19.3750',
                '5' => '22.5000',
            ),
            '7'  => array(
                '1' => '15.6250',
                '2' => '16.8750',
                '3' => '18.1250',
                '4' => '19.3750',
                '5' => '22.5000',
            ),
            '8'  => array(
                '1' => '15.6250',
                '2' => '16.8750',
                '3' => '18.1250',
                '4' => '19.3750',
                '5' => '22.5000',
            ),
            '9'  => array(
                '1' => '15.6250',
                '2' => '16.8750',
                '3' => '18.1250',
                '4' => '19.3750',
                '5' => '22.5000',
            ),
            '10' => array(
                '1' => '0.0000',
                '2' => '22.5000',
                '3' => '0.0000',
                '4' => '25.0000',
                '5' => '30.0000',
            ),
            '11' => array(
                '1' => '15.6250',
                '2' => '22.5000',
                '3' => '18.1250',
                '4' => '25.0000',
                '5' => '30.0000',
            ),
            '12' => array(
                '1' => '0.0000',
                '2' => '0.0000',
                '3' => '0.0000',
                '4' => '0.0000',
                '5' => '0.0000',
            ),
            '13' => array(
                '1' => '16.8750',
                '2' => '18.1250',
                '3' => '19.3750',
                '4' => '20.6250',
                '5' => '23.7500',
            )
        );
        foreach ($mapper as $tId => $values)
        {
            foreach ($values as $taId => $price)
            {
                $this->insert(
                    'transcription_prices',
                    array(
                        'transcription_type_id' => $tId,
                        'turnaround_time_id'    => $taId,
                        'price'                 => $price
                    )
                );
            }
        }
	}

	public function safeDown()
	{
        $this->truncateTable('transcription_prices');
	}
}