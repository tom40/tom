<?php

class m130802_085712_substandard_payrate extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'audio_jobs_typists', 'substandard_payrate', "enum('0', '1') DEFAULT '0' AFTER `current`" );
        $this->addColumn( 'audio_jobs_typists_shadow', 'substandard_payrate', "enum('0', '1') DEFAULT '0'" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'audio_jobs_typists', 'substandard_payrate' );
        $this->dropColumn( 'audio_jobs_typists_shadow', 'substandard_payrate' );
    }
}