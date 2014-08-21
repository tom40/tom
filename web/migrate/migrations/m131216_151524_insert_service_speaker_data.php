<?php

class m131216_151524_insert_service_speaker_data extends CDbMigration
{
    public function safeUp()
    {
        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 1,
                      'speaker_number_id' => 1,
                      'percentage'        => 0
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 1,
                      'speaker_number_id' => 2,
                      'percentage'        => 6.95
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 2,
                      'speaker_number_id' => 1,
                      'percentage'        => 0
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 2,
                      'speaker_number_id' => 2,
                      'percentage'        => 6.95
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 3,
                      'speaker_number_id' => 1,
                      'percentage'        => 0
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 3,
                      'speaker_number_id' => 2,
                      'percentage'        => 14.3
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 4,
                      'speaker_number_id' => 1,
                      'percentage'        => 0
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 4,
                      'speaker_number_id' => 2,
                      'percentage'        => 5.88
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 7,
                      'speaker_number_id' => 1,
                      'percentage'        => 0
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 7,
                      'speaker_number_id' => 2,
                      'percentage'        => 11
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 8,
                      'speaker_number_id' => 1,
                      'percentage'        => 0
                 )
        );

        $this->insert(
             's_service_speaker_number',
                 array(
                      'service_id'        => 8,
                      'speaker_number_id' => 2,
                      'percentage'        => 11
                 )
        );
    }

    public function safeDown()
    {
        $this->truncateTable( 's_service_speaker_number' );
    }
}