<?php

/**
 * Group Controller
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: GroupController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */

/**
 * Group Controller
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: GroupController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */
class GroupController extends App_Controller_Action
{
    const EMAIL_PROOFREADER              = 'proofreader';
    const EMAIL_TYPIST                   = 'typist';
    const EMAIL_SUCCESS_MESSAGE          = 'Your email has been succesfully sent';
    const EMAIL_DEFAULT_ERROR_MESSAGE    = 'Unable to send your email';
    const EMAIL_SEND_ERROR_MESSAGE       = 'Error sending email';
    const EMAIL_NO_LIST_MESSAGE          = 'There are no users with the selected options to email';
    const EMAIL_VALIDATION_ERROR_MESSAGE = 'There where validation errors, please complete all required options!';

    /**
     * Email sent flag
     *
     * @var bool
     */
    protected $_emailSent          = false;

    /**
     * Number of emails sent
     *
     * @var int
     */
    protected $_sentEmailsCount    = 0;

    /**
     * Group email object
     *
     * @var Application_Model_Group
     */
    private   $_groupModel;

    /**
     * selected typists list
     *
     * @var array
     */
    protected $_typistSelectionList;

    /**
     * selected proofreaders list
     *
     * @var array
     */
    protected $_proofreaderSelectionList;

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        /* Initialize action controller here */
        $this->_groupModel               = new Application_Model_Group();
        $this->flashMessenger            = $this->_helper->flashMessenger;
        $this->_typistSelectionList      = array();
        $this->_proofreaderSelectionList = array();
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
    	// action body
    	$request = $this->getRequest();
    	$form    = new Application_Form_GroupEmail();

        $emailSent = false;

        if ($this->getRequest()->isPost()) {
    		$formData     = $this->_request->getPost();
            $extraWork    = ($formData['extra_work'] == 'yes') ? true : false;
            $messageTitle = $formData['message_title'];
            $messageBody  = $formData['message_body'];

            // Temporary store selected typists and proofreaders
            $this->_typistSelectionList      = isset($formData['typistSelectionList']) ? $formData['typistSelectionList'] : array();
            $this->_proofreaderSelectionList = isset($formData['proofreaderSelectionList']) ? $formData['proofreaderSelectionList'] : array();

            if ($form->isValid($formData))
            {
                if (empty($this->_typistSelectionList) && empty($this->_proofreaderSelectionList))
                {
                    $this->flashMessenger->addMessage(array('error' => self::EMAIL_NO_LIST_MESSAGE));
                }
                else
                {
                    $userMailList = array_merge($this->_typistSelectionList, $this->_proofreaderSelectionList);
                    if (!empty($userMailList))
                    {
                        $this->_prepareAndSendEmail($userMailList, $messageTitle, $messageBody, $extraWork);
                    }

                    if ($this->_emailSent)
                    {
                        // Success
                        $successMessage = self::EMAIL_SUCCESS_MESSAGE . " (sent to {$this->_sentEmailsCount} users)";
                        $this->_helper->FlashMessenger(array('notice' => $successMessage));
                    }
                    else
                    {
                        // Error
                        $this->_helper->FlashMessenger(array('warning' => self::EMAIL_DEFAULT_ERROR_MESSAGE));
                    }
                }
            }
            else
            {
                $this->flashMessenger->addMessage(array('error' => self::EMAIL_VALIDATION_ERROR_MESSAGE));
                $form->populate($formData);
            }

            // If shift times have been selected display the dropdowns on submit
            if (isset($formData['proofreader_shift_time'])
            && in_array(self::EMAIL_PROOFREADER, $formData['user_type'])
            && !empty($formData['on_shift']))
            {
                $this->view->showProofreaderShiftTimeSelector = true;
            }

            if (isset($formData['typist_shift_time'])
            && in_array(self::EMAIL_TYPIST, $formData['user_type'])
            && !empty($formData['on_shift']))
            {
                $this->view->showTypistShiftTimeSelector = true;
            }

            $this->view->typistSelectionList      = $this->_typistSelectionList;
            $this->view->proofreaderSelectionList = $this->_proofreaderSelectionList;
            $this->_selectedEmailList($formData);
        }

        $emailList                  = array();
        $this->view->emailList      = $emailList;
		$this->view->form           = $form;

        // Set whether to show typist/proofreader shift times
        $this->_setShiftTimesAvailability();
    }

    /**
     * Check whether typists/proofreaders have any shift times for todays date
     *
     * @return void
     */
    protected function _setShiftTimesAvailability()
    {
        $todaysDate             = date('N', strtotime(date('Y-m-d')));
        $typistShiftMapper      = Application_Model_DefaultShiftMapperFactory::getObject(Application_Model_UsersShiftMapper::TYPIST_SHIFT);
        $proofreaderShiftMapper = Application_Model_DefaultShiftMapperFactory::getObject(Application_Model_UsersShiftMapper::PROOFREADER_SHIFT);

        $this->view->typistHasShiftTimes      = $typistShiftMapper->hasShiftTimes($todaysDate);
        $this->view->proofreaderHasShiftTimes = $proofreaderShiftMapper->hasShiftTimes($todaysDate);
    }

    /**
     * Prepare sending email to user type
     *
     * @param array $mailList
     * @param string $messageTitle
     * @param string $messageBody
     * @param array $extraWork
     *
     * @return bool
     */
    protected function _prepareAndSendEmail($mailList, $messageTitle, $messageBody, $extraWork)
    {
        $response = $this->_sendEmail($mailList, $messageTitle, $messageBody, $extraWork);
        if (true === $response)
        {
            $this->_emailSent = true;
            return true;
        }

        $this->_helper->FlashMessenger(array('warning' => self::EMAIL_SEND_ERROR_MESSAGE . ' to ' . $userType));
        return false;
    }

    /**
     * Send group email
     *
     * @param array  $groupList the list of people to send the email to
     * @param string $messageTitle  Email message title
     * @param string $messageBody   Email message body
     * @param bool   $extraWork     Include extra work message
     *
     * @return bool
     */
    protected function _sendEmail($groupList, $messageTitle, $messageBody, $extraWork = false)
    {
        // Send email
        if (!empty($groupList))
        {
            $groupMail = new App_Mail_GroupEmail();
            $groupMail->setView($this->view);

            // Fetch list of audio jobs
            if ($extraWork)
            {
                $groupMail->setAudioJobs($this->_getExtraWorkDetails());
            }

            foreach($groupList as $userEmail)
            {
                $this->_sentEmailsCount++;
                $groupMail->addBcc($userEmail);
            }

            $groupMail->setSubject($messageTitle);
            $groupMail->setMessageBody($messageBody);

            if ($groupMail->sendMail())
            {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Returns a work list with unassigned and approved jobs
     *
     * @return array
     */
    protected function _getExtraWorkDetails()
    {
        return $this->_groupModel->getExtraWorkDetails();
    }

    /**
     * Set filters for the group we are emailing
     *
     * @param array $formData the posted form data
     *
     * @return void
     */
    protected function _setFilterOptions($formData)
    {
        if (isset($formData['proofreader_shift_time']))
        {
            $this->_groupModel->setFilter(Application_Model_Group::FILTER_PR_SHIFT_ID, $formData['proofreader_shift_time']);
        }

        if (isset($formData['typist_shift_time']))
        {
            $this->_groupModel->setFilter(Application_Model_Group::FILTER_TYPIST_SHIFT_ID, $formData['typist_shift_time']);
        }

        if (isset($formData['on_shift']))
        {
            $this->_groupModel->setFilter(Application_Model_Group::FILTER_SHIFT, $formData['on_shift']);
        }

        if (isset($formData['trained_in']))
        {
            $this->_groupModel->setFilter(Application_Model_Group::FILTER_TRAINING, $formData['trained_in']);
        }

        if (isset($formData['typist_grade']))
        {
            $this->_groupModel->setFilter(Application_Model_Group::FILTER_TYPIST_GRADE, $formData['typist_grade']);
        }

        if (isset($formData['proofreader_grade']))
        {
            $this->_groupModel->setFilter(Application_Model_Group::FILTER_PROOFREADER_GRADE, $formData['proofreader_grade']);
        }
    }

    /**
     * Filtered Emails action
     *
     * @return void
     */
    public function ajaxEmailFilterAction()
    {
        $this->view->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

    	// action body
        if ($this->getRequest()->isPost()) {
    		$formData     = $this->getRequest()->getPost();
            if (isset($formData['user_type']))
            {
                $this->_selectedEmailList($formData);
            }
        }

        $this->view->selectAll = true;
        $output           = array();
        $outputStatus     = 'ok';
        $outputHtml       = $this->view->render('group/ajax-email-filter.phtml');
    	$output['status'] = $outputStatus;
    	$output['html']   = $outputHtml;
    	echo json_encode($output);
    }

    /**
     * Selected Email List
     *
     * @param array $formData
     *
     * @return void
     */
    protected function _selectedEmailList($formData)
    {
        $proofreaders = array();
        $typists      = array();

        if (isset($formData['user_type']))
        {
            $userTypes = $formData['user_type'];

            // Remove ALL from the user types list
            if (in_array('all', $userTypes))
            {
                $removeKeys = array_keys($userTypes, 'all');
                if (!empty($removeKeys))
                {
                    foreach ($removeKeys as $key)
                    {
                        unset($userTypes[$key]);
                    }
                }
            }

            if (!empty($userTypes))
            {
                foreach ($userTypes as $userType)
                {
                    $this->_setFilterOptions($formData);

                    if (self::EMAIL_PROOFREADER === $userType)
                    {
                        $proofreaders = $this->_groupModel->getProofreaders();
                    }
                    elseif (self::EMAIL_TYPIST === $userType)
                    {
                        $typists = $this->_groupModel->getTypists();
                    }
                }
            }
        }

        $this->view->filteredTypists      = $typists;
		$this->view->filteredProofreaders = $proofreaders;
    }

    /**
     * Filtered Emails action
     *
     * @return void
     */
    public function ajaxExtraWorkAction()
    {
        $this->view->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

        $extraWork = $this->_getExtraWorkDetails();

        $this->view->extraWork = $extraWork;
        $outputStatus     = 'ok';
        $outputHtml       = $this->view->render('group/ajax-extra-work.phtml');
    	$output['status'] = $outputStatus;
    	$output['html']   = $outputHtml;
    	echo json_encode($output);
    }
}

