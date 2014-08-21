<?php

class m140217_011707_service_descriptions extends CDbMigration
{

	public function safeUp()
	{
        $descriptions = array(
            1 => 'This is our most popular service. Verbatim transcription but edited for a cleaner read. Speech is unaltered except umms, ahhs, false starts and repetition are removed. Male/female differentiation and 10 min timecodes included as standard.',
            2 => 'Summary transcripts are simply condensed fulls, but this services benefits from highlighted verbatim quotes to that you can simply extract the key points. All repetition is removed, and dialogue condensed for a cleaner read.',
            3 => 'Summary transcripts are simply condensed fulls. All repetition is removed, and dialogue condensed for a cleaner read.',
            4 => 'Notes are written in real time as if the notetaker is present. Short, sharp, to the point.',
        );

        foreach( $descriptions as $id => $description )
        {
            $this->update(
                's_service',
                array(
                    'description' => $description
                ),
                'id = :id',
                array( ':id' => $id )
            );
        }

        $descriptions = array(
            1 => 'Word for word transcription. A carbon copy, including all umms, ahhs and repetition. Male/female differentiation and 10 min timecodes as standard.',
            5 => 'Email us at bookings@takenotetyping.com, and we will set you up with a pin so that you can simply call someone and have the recording of the call sent directly to us, to be transcriber (or just recorded).',
            3 => 'If your audio contains Medical terminology please tick this box to ensure that our experienced Medical grade transcribers work on the file.',
            2 => 'Identities of each speaker each time they talk, this requires us to have the Video in order be able to identify them.',
            4 => 'Timecodes will be added every 30 seconds and with each utterance or interruption.',
        );

        foreach( $descriptions as $id => $description )
        {
            $this->update(
                 's_price_modifier',
                     array(
                         'description' => $description
                     ),
                     'id = :id',
                     array( ':id' => $id )
            );
        }
	}

	public function safeDown()
	{
        return true;
	}
}