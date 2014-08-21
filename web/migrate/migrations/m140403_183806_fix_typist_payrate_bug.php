<?php

class m140403_183806_fix_typist_payrate_bug extends CDbMigration
{

	public function safeUp()
	{
        $model = new Application_Model_AudioJobMapper();
        $select = $model->select()
            ->from( array( 'aj' => 'audio_jobs' ) )
            ->joinLeft( array( 'ajt' => 'audio_jobs_typists' ), 'ajt.audio_job_id = aj.id', array() )
            ->where( 'ajt.pay_per_minute IS NULL' )
            ->where( 'ajt.current = 1' )
            ->where( 'ajt.user_id > 0' )
            ->where( "ajt.due_date > '2013-12-06 06:00:00'" )
            ->group( 'aj.id' );

        $audioJobs = $model->fetchAll( $select );

        //var_dump( count( $audioJobs ) );
        //return false;
        foreach ( $audioJobs as $audioJob )
        {
            $audioJob->updateTypistRates();
        }
	}

	public function safeDown()
	{
        return true;
	}
}