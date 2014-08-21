<?php

class m130317_204015_typist_pay_rate_saved extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('audio_jobs_typists', 'pay_per_minute', 'INT(11) DEFAULT 0');
        $this->addColumn('audio_jobs_typists_shadow', 'pay_per_minute', 'INT(11) DEFAULT 0');
    }

    public function safeDown()
    {
        $this->dropColumn('audio_jobs_typists', 'pay_per_minute');
        $this->dropColumn('audio_jobs_typists_shadow', 'pay_per_minute');
    }
}