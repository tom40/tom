<?php

class ArchiveController extends Zend_Controller_Action
{

    /**
     * Init
     *
     * Disable layout and call parent::init()
     *
     * @return void
     */
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        parent::init();
    }

    /**
     * Archive completed projects older than 3 months
     *
     * @return void
     */
    public function indexAction()
    {
        $jobMapper = new Application_Model_JobMapper();
        $data = $jobMapper->archiveOldJobs();

        $log = '';
        foreach ( $data as $array )
        {
            $log .= 'Project' . PHP_EOL . PHP_EOL
                .  $array['id'] . ': ' . $array['title'] . ' '
                . $array['po_number'] . ' ' . $array['job_due_date'] . PHP_EOL . PHP_EOL
                . "\t" . 'Audio Jobs' . PHP_EOL . PHP_EOL;

            if ( isset ( $array['audio_jobs_archived'] )  && is_array( $array['audio_jobs_archived'] ) )
            {
                foreach ( $array['audio_jobs_archived'] as $audioJob )
                {
                    //var_dump( $audioJob ); die;
                    $log .= "\t" . $audioJob['id'] . ': ' . $audioJob['file_name'] . ' ';
                    $log .= ( isset( $audioJob['file_deleted'] ) && $audioJob['file_deleted'] ) ? 'DELETED' : 'NOT DELETED';
                    $log .= PHP_EOL;
                }
            }

            $log .= PHP_EOL
                . '-----------------------------------------------'
                . PHP_EOL;
        }

        $file = APPLICATION_PATH . '/../logs/archive.log';
        file_put_contents( $file, print_r( $log, true ) );
    }

    /**
     * Check archived files
     *
     * @return void
     */
    public function purgeAction()
    {
        $jobMapper = new Application_Model_JobMapper();
        $data = $jobMapper->purgeOldJobs();
        foreach ( $data as $row )
        {
            echo $row . "\n";
        }
    }

    /**
     * Iterate through files and delete if job is, or should be archived.
     *
     * @return void
     */
    public function purgeFilesAction()
    {
        $mapper = new Application_Model_AudioJobMapper();
        $data = $mapper->purgeDeletedFiles();
        foreach ( $data as $row )
        {
            echo $row . "\n";
        }
    }

    /**
     * Purge files with no record
     *
     * @return void
     */
    public function purgeFilesNoRecordAction()
    {
        $mapper = new Application_Model_AudioJobMapper();
        $data   = $mapper->purgeFilesNoRecords();
        foreach ( $data as $row )
        {
            echo $row . "\n";
        }
    }
}