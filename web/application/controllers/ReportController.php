<?php
/**
 * Report Controller
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    App
 * @subpackage App
 * @copyright  Copyright (c) 2012-2012 Take Note Typing
 * @version    $Id$
 * @since      1.0
 */

/**
 * Report Controller
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    App
 * @subpackage App
 * @copyright  Copyright (c) 2012-2012 Take Note Typing
 * @version    $Id$
 * @since      1.0
 */
class ReportController extends App_Controller_Action
{
    const ACTION_SAVE          = 'save';
    const ACTION_SAVE_AND_SEND = 'saveandsend';

    protected $_reportModel;

    public function init()
    {
        parent::init();
        $this->flashMessenger = $this->_helper->flashMessenger;
        $this->_reportModel   = new Application_Model_Report();
    }

    /**
     * Select action for selecting the typist for an audio file job with multiple typists assigned
     *
     * @return void
     */
    public function selectAction()
    {
        $audioJobId = $this->_request->getParam('ajid');

        $audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
        $audioJobMapper       = new Application_Model_AudioJobMapper();
        $audioJob             = $audioJobMapper->fetchById($audioJobId);
        $audioJobTypists      = $audioJobTypistMapper->fetchByAudioJobId($audioJobId);

        $this->view->audioJob = $audioJob;
        $this->view->typists  = $audioJobTypists;
    }

    /**
     * Typist viewing report
     *
     * @return void
     */
    public function viewAction()
    {
        $reportId       = $this->_request->getParam('id');
        if ($this->_reportModel->isReportCompleted($reportId))
        {
            $reportCriteria = $this->_reportModel->getCriteria($reportId);
            $reportInfo     = $this->_reportModel->fetchReport($reportId);

            $typistId = $reportInfo['typist_id'];
            $currentUserId = Zend_Auth::getInstance()->getIdentity()->id;

            $canView = false;
            if ($currentUserId === $typistId)
            {
                $canView = true;
            }

            $this->view->reportInfo     = $reportInfo;
            $this->view->reportCriteria = $reportCriteria;
            $this->view->canView        = $canView;

            $this->_defaultData();
        }
    }

    /**
     * Update action (admin)
     *
     * @return void
     */
    public function updateAction()
    {
        $typistId   = $this->_request->getParam('tid');
        $audioJobId = $this->_request->getParam('ajid');

        // Fetch audio job details
        $audioJobMapper = new Application_Model_AudioJobMapper();
        $audioJob       = $audioJobMapper->fetchById($audioJobId);

        // Get reportId for this typist and audiojob id
        $reportId       = $this->_reportModel->getTypistReport($typistId, $audioJobId, true);
        $reportCriteria = $this->_reportModel->getCriteria($reportId);

        $formErrors = array();
        $formData   = array();
        if ($this->_request->isPost())
        {
            $formData        = $this->_request->getPost();
            $action          = $formData['action'];
            $finalComment    = $formData['summary'];
            $form            = new Application_Form_Report();
            foreach ($formData as $group => &$item)
            {
                if (is_array($item))
                {
                    $criteriaId = $item['id'];
                    $score      = $item['score'];
                    $comment    = $item['comment'];

                    // Setup for validation and saving
                    $data            = array();
                    $data['score']   = $score;
                    $data['comment'] = $comment;

                    if ($form->isValid($data))
                    {
                        if ($this->_reportModel->isValidScore($criteriaId, $score))
                        {
                            $this->_reportModel->saveReportData($reportId, $criteriaId, $data);
                        }
                        else
                        {
                            $formErrors[$criteriaId]['score'] = 'The selected score exceeds the max score';
                        }
                    }
                    else
                    {
                        $formErrors = $this->_getFormErrors($criteriaId, $form, $formErrors);
                    }
                }

                $this->_reportModel->updateReportSummary($reportId, $finalComment);
            }

            if (empty($formErrors))
            {
                if (self::ACTION_SAVE_AND_SEND == $action)
                {
                    // Send notification to the user
                    $httpHost = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
                    $appUrl   = $httpHost . $this->getRequest()->getBaseUrl();

                    // Generate report url for email
                    $reportRelativeUrl = $this->view->url(array('controller' => 'report', 'action' => 'view', 'id' => $reportId ), null, true);
                    $reportUrl         = $appUrl . '/' . $reportRelativeUrl;

                    $userMapper  = new Application_Model_UserMapper();
                    $jobMapper   = new Application_Model_JobMapper();

                    $user = $userMapper->fetchById($typistId);
                    $job  = $jobMapper->fetchById($audioJob['job_id']);

                    // Set report as complete
                    $this->_reportModel->setReportComplete($reportId);

                    $reportEmail = new App_Mail_ReportEmail();
                    $reportEmail->setView($this->view);
                    $reportEmail->setReceiver($user['email'])
                        ->setReportId($reportId)
                        ->setProjectFileName($job['title'])
                        ->setAudioJobFilename($audioJob['file_name'])
                        ->setSiteUrl($appUrl)
                        ->setReportUrl($reportUrl);

                    if ($reportEmail->sendMail())
                    {
                        $this->flashMessenger->addMessage(array('notice' => "Report succesfully saved and the user has been notified"));
                    }
                    else
                    {
                        $this->flashMessenger->addMessage(array('notice' => "Report succesfully saved"));
                        $this->flashMessenger->addMessage(array('warning' => "Unable to notify user via email"));
                    }
                }
                else if (self::ACTION_SAVE == $action)
                {
                    $this->flashMessenger->addMessage(array('notice' => "Report succesfully saved"));
                }

            }
            else
            {
                if (self::ACTION_SAVE == $action)
                {
                    $this->flashMessenger->addMessage(array('warning' => "The report has been saved, however there are some errors below"));
                }
                else
                {
                    $this->flashMessenger->addMessage(array('error' => "There are errors saving the report"));
                }

                // The report is no longer complete
                $this->_reportModel->setReportComplete($reportId, 0);
            }
        }
        else
        {
            $formData = $this->_populateReportCriteriaData($reportCriteria, $formData);
        }

        $reportInfo = $this->_reportModel->fetchReport($reportId);
        $this->view->reportCriteria = $reportCriteria;
        $this->view->formErrors     = $formErrors;
        $this->view->formData       = $formData;
        $this->view->reportInfo     = $reportInfo;
        $this->view->audioJob       = $audioJob;

        if (Zend_Auth::getInstance()->getIdentity()->acl_group_id == 2)
        {
            $this->view->isProofreader = true;
        }

        $this->_defaultData();
    }

    /**
     * Custom form errors checker
     *
     * @return arrat
     */
    protected function _getFormErrors($criteriaId, $form, $errors)
    {
        $formErrors = $form->getErrors();
        foreach ($formErrors as $key => $error)
        {
            if (!empty($error))
            {
                $errors[$criteriaId][$key] = $error[0];
            }
        }

        return $errors;
    }

    /**
     * Custom report criteria data populator
     *
     * @return void
     */
    protected function _populateReportCriteriaData($reportCriteria)
    {
        foreach ($reportCriteria as $report)
        {
            $identifier = 'area_' . $report['id'];
            $formData[$identifier]['score']   = $report['userScore'];
            $formData[$identifier]['comment'] = $report['comment'];
        }
        return $formData;
    }

    /**
     * Sets default data used by multiple actions
     *
     * @return void
     */
    protected function _defaultData()
    {
       $this->view->maxScore = $this->_reportModel->getMaxScore();
    }

}

