<?php

class m131111_115358_fix_audio_job_deleted extends CDbMigration
{
	public function safeUp()
	{
        $audioJobModel = new Application_Model_AudioJobMapper();
        $select        = $audioJobModel->getAdapter()->select();

        $select->from( array( 'aj' => 'audio_jobs' ), array( 'aj.id' ) )
            ->join( array( 'j' => 'jobs' ), 'aj.job_id = j.id', array() )
            ->where( 'aj.archived = "1"' )
            ->where( 'j.archived = "0"' );

        $audioJobs = $audioJobModel->getAdapter()->fetchAll( $select );

        foreach ( $audioJobs as $audioJobArray )
        {
            $audioJob          = $audioJobModel->fetchRow( "id = '" . $audioJobArray['id'] . "'" );
            $audioJob->deleted = date( 'Y-m-d H:i:s' );
            $audioJob->save();
        }

	}

	public function safeDown()
	{
	}
}