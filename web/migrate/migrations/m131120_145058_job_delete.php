<?php

class m131120_145058_job_delete extends CDbMigration
{

    public function safeUp()
    {
        $this->addColumn( 'jobs', 'deleted', "DATETIME DEFAULT NULL" );
        $this->addColumn( 'jobs_shadow', 'deleted', "DATETIME DEFAULT NULL" );
    }

    public function safeDown()
    {
        $this->dropColumn( 'jobs', 'deleted' );
        $this->dropColumn( 'jobs_shadow', 'deleted' );
    }
}