<?php

class m130711_080911_transcription_training_codes extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'lkp_transcription_types', 'training_code', "VARCHAR(5) DEFAULT NULL" );
        $this->createIndex( 'training_code', 'lkp_transcription_types', 'training_code' );
    }

    public function safeDown()
    {
        $this->dropColumn( 'lkp_transcription_types', 'training_code' );
    }
}