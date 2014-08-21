<?php

class m131120_150502_fix_job_delete extends CDbMigration
{
	public function safeUp()
	{
        ini_set('memory_limit','755M');
        $jobModel = new Application_Model_JobMapper();
        $select   = $jobModel->select();

        $select->from( array( 'j' => 'jobs' ), array( 'j.id' ) )
            ->where( 'j.archived = "1"' )
            ->where( 'j.invoiced_date IS NULL' );

        $jobs = $jobModel->fetchAll( $select );

        foreach ( $jobs as $jobArray )
        {
            $data = array(
                'id'       => $jobArray['id'],
                'deleted'  => date( 'Y-m-d H:i:s' ),
                'archived' => 0
            );
            $jobModel->save( $data );
            $jobModel->deleteAudioJobs( $jobArray['id'] );
        }
	}

	public function safeDown()
	{

	}
}