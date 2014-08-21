<?php

class m131206_111757_project_status extends CDbMigration
{
	public function safeUp()
	{
        $this->insert(
             'lkp_job_statuses',
             array(
                'id'         => 7,
                'name'       => 'Pending Invoice',
                'sort_order' => 6,
                'complete'   => 1
            )
        );

        $this->update(
            'lkp_job_statuses',
            array( 'sort_order' => 7 ),
            'id = "5"'
        );
	}

	public function safeDown()
	{
        $this->delete(
             'lkp_job_statuses',
             'id = :id',
             array( ':id' => 7 )
        );
        $this->update(
             'lkp_job_statuses',
             array( 'sort_order' => 6 ),
             'id = "7"'
        );
	}
}