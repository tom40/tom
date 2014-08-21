<?php
class JobDueDateController extends Zend_Controller_Action
{
    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function manageDueDateAction()
    {
        $jobMapper = new Application_Model_JobMapper();
        $jobs      = $jobMapper->fetchJobsNearingCompletion();
        if ( count( $jobs ) > 0 )
        {
            var_dump( $jobs );
        }
    }

}