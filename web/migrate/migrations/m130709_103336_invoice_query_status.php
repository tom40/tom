<?php

class m130709_103336_invoice_query_status extends CDbMigration
{

	public function safeUp()
	{
        $this->update( 'lkp_job_statuses', array( 'sort_order' => '6' ), "sort_order = '5'" );
        $this->update( 'lkp_job_statuses', array( 'sort_order' => '5' ), "sort_order = '4'" );
        $this->insert(
            'lkp_job_statuses',
            array(
                'name'       => 'Invoice Enquiry',
                'sort_order' => '4'
            )
        );
	}

	public function safeDown()
	{
        $this->delete( 'lkp_job_statuses', 'name = :name', array( ':name' => 'Invoice Enquiry' ) );
        $this->update( 'lkp_job_statuses', array( 'sort_order' => '4' ), "sort_order = '5'" );
        $this->update( 'lkp_job_statuses', array( 'sort_order' => '5' ), "sort_order = '6'" );
	}
}