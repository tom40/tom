<?php

class m131216_135818_insert_service_data extends CDbMigration
{

	public function safeUp()
	{
        $this->insert(
            's_service',
            array(
                 'id'         => 1,
                 'name'       => 'FULL TRANSCRIPTION (Intelligent Verbatim)',
                 'base_price' => 65.00
            )
        );

        $this->insert(
             's_service',
                 array(
                      'id'         => 2,
                      'name'       => 'SUMMARY INC. QUOTES',
                      'base_price' => 65.00
                 )
        );

        $this->insert(
             's_service',
                 array(
                      'id'         => 3,
                      'name'       => 'SUMMARY',
                      'base_price' => 52.00
                 )
        );

        $this->insert(
             's_service',
                 array(
                      'id'         => 4,
                      'name'       => 'NOTES & QUOTES',
                      'base_price' => 45.00
                 )
        );

        $this->insert(
             's_service',
                 array(
                      'id'         => 5,
                      'name'       => 'MEDIA: POST PRODUCTION STYLE A (TX01)',
                      'base_price' => 146.00
                 )
        );

        $this->insert(
             's_service',
                 array(
                      'id'         => 6,
                      'name'       => 'MEDIA: POST PRODUCTION STYLE B (TX02)',
                      'base_price' => 200.00
                 )
        );

        $this->insert(
             's_service',
                 array(
                      'id'         => 7,
                      'name'       => 'MEDIA: TIMECODED (Intelligent Verbatim)',
                      'base_price' => 111.00
                 )
        );

        $this->insert(
             's_service',
                 array(
                      'id'         => 8,
                      'name'       => 'MEDIA: TIMECODED (Verbatim)',
                      'base_price' => 125.00
                 )
        );
	}

	public function safeDown()
	{
        $this->execute('SET foreign_key_checks = 0');
        $this->truncateTable( 's_service' );
        $this->execute('SET foreign_key_checks = 1');
	}
}