<?php

class m140119_051939_sub_standard extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'audio_jobs_typists', 'replacement_payrate', "enum('0', '1') DEFAULT '0' AFTER `substandard_payrate`" );
        $this->addColumn( 'audio_jobs_typists_shadow', 'replacement_payrate', "enum('0', '1') DEFAULT '0'" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'audio_jobs_typists', 'replacement_payrate' );
        $this->dropColumn( 'audio_jobs_typists_shadow', 'replacement_payrate' );
    }
}