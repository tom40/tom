<?php

class m130812_141309_invoiced_date extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn( 'jobs', 'invoiced_date', "DATETIME AFTER `archived`" );
        $this->addColumn( 'jobs', 'archived_date', "DATETIME AFTER `invoiced_date`" );
        $this->addColumn( 'jobs_shadow', 'invoiced_date', "DATETIME" );
        $this->addColumn( 'jobs_shadow', 'archived_date', "DATETIME" );

        $this->update( 'jobs', array( 'invoiced_date' => date( "Y-m-d H:i:s" ) ), 'status_id = :status_id', array( 'status_id' => 5 ) );
    }

    public function safeDown()
    {
        $this->dropColumn( 'jobs', 'invoiced_date' );
        $this->dropColumn( 'jobs_shadow', 'invoiced_date' );

        $this->dropColumn( 'jobs', 'archived_date' );
        $this->dropColumn( 'jobs_shadow', 'archived_date' );
    }
}