<?php

/**
 * Shift Booker Controller
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    Application
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: ShiftBookerController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */

/**
 * Shift Booker Controller
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    Application
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: ShiftBookerController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */
class ShiftBookerController extends App_Controller_Action
{
    protected $_selectedDate;

    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * List action
     *
     * @return void
     */
    public function listAction()
    {
        $canFilterByAbility = false;
        $form = new Application_Form_AdminShiftBooker();

        $shiftDate = date('Y-m-d');
        $userType  = Application_Model_UsersShiftMapper::TYPIST_SHIFT;

        $filter   = array();
        $weekView = false;
        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();

            if (isset($formData['week_view']) && true == $formData['week_view'])
            {
                $weekView = true;
            }

            if (!empty($formData['shift_date']))
            {
                $shiftDate = date('Y-m-d', strtotime(str_replace('/','-',$formData['shift_date'])));
            }

            $userType       = $formData['user_type'];
            $name           = $formData['name'];
            $filter['name'] = $name;

            if (isset($formData['shift_status']))
            {
                $filter['shift_status'] = $formData['shift_status'];
            }

            if (isset($formData['ability']))
            {
                $filter['ability'] = $formData['ability'];
            }

            if (isset($formData['grade']))
            {
                $filter['grade'] = $formData['grade'];
            }

            // Specific shift select
            if (isset($formData['date-shift-select']) && !empty($formData['date-shift-select']))
            {
                $filter['shift_id']          = $formData['date-shift-select'];
                $this->view->selectedShiftId = $formData['date-shift-select'];
            }

            $form->populate($formData);
        }

        $form->getElement('shift_date')->setValue($shiftDate);

        if ($userType == Application_Model_UsersShiftMapper::TYPIST_SHIFT)
        {
            $canFilterByAbility = true;
        }

        $shiftMapper        = Application_Model_ShiftMapperFactory::getObject($userType);
        $defaultShiftMapper = Application_Model_DefaultShiftMapperFactory::getObject($userType);

        $defaultShiftDays  = $defaultShiftMapper->fetchAvailableShiftDays($shiftDate, $weekView);
        $defaultShiftTimes = $defaultShiftMapper->fetchAvailableShiftTimes($shiftDate, $weekView);

        // Fetch shifts
        $shiftData = $shiftMapper->fetchShiftsByDate($shiftDate, $filter, $weekView);

        $this->view->shiftData          = $shiftData;
        $this->view->canFilterByAbility = $canFilterByAbility;
        $this->view->userType           = $userType;
        $this->view->form               = $form;
        $this->view->noRecordsFound     = count($shiftData);
        $this->view->noColsPerRow       = count($defaultShiftTimes);
        $this->view->defaultShiftDays   = $defaultShiftDays;
        $this->view->defaultShiftTimes  = $defaultShiftTimes;
    }

    /**
     * Manage shift times action
     *
     * @return void
     */
    public function manageShiftsAction()
    {
        $form                          = new Application_Form_ManageDefaultShift();
        $typistDefaultShiftMapper      = new Application_Model_TypistsDefaultShiftMapper();
        $proofreaderDefaultShiftMapper = new Application_Model_ProofreadersDefaultShiftMapper();

        if ($this->getRequest()->isPost()) {
            $userType = $this->_request->getParam('user_type');
            $formData = $this->getRequest()->getPost();
    		$this->_processShiftForm(null, $form, $formData, $userType);
        }

        $this->view->typistShifts      = $typistDefaultShiftMapper->fetchAvailableShiftTimes();
        $this->view->proofreaderShifts = $proofreaderDefaultShiftMapper->fetchAvailableShiftTimes();
        $this->view->form              = $form;
        $this->view->formAction        = 'new';
    }

    /**
     * Update shift times action
     *
     * @return void
     */
    public function updateShiftAction()
    {
        $form = new Application_Form_ManageDefaultShift();

        $userType = $this->_request->getParam('usertype');
        $shiftId  = $this->_request->getParam('id');

        $defaultShiftMapper = Application_Model_DefaultShiftMapperFactory::getObject($userType);

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
    		$this->_processShiftForm($shiftId, $form, $formData, $userType);
        }
        else
        {
            $shift = $defaultShiftMapper->fetchCurrentShift($shiftId);
            $form->populate($shift);
            $form->getElement('start_day')->setValue($shift['start_day_number']);
            $form->getElement('end_day')->setValue($shift['end_day_number']);
        }

        $shift = $defaultShiftMapper->fetchCurrentShift($shiftId);
        $form->getElement('user_type')->setValue($userType);

        $this->view->form       = $form;
        $this->view->shift      = $shift;
        $this->view->userType   = $userType;
        $this->view->formAction = 'update';
    }

    /**
     * Delete Shift action
     *
     * @return void
     */
    public function deleteShiftAction()
    {
        $userType = $this->_request->getParam('usertype');
        $shiftId  = $this->_request->getParam('id');

        if (!empty($userType) && !empty($shiftId))
        {
            $defaultShiftMapper = Application_Model_DefaultShiftMapperFactory::getObject($userType);
            $defaultShiftMapper->removeShift($shiftId);

            $this->_helper->FlashMessenger(array('warning' => 'Shift has been removed'));
            // Redirect to edit page
            $url = $this->view->url(array('controller' => 'shift-booker', 'action' => 'manage-shifts'), null, true);

        }
        else
        {
            $this->_helper->FlashMessenger(array('error' => 'There was an error deleting shift, please contact system administrator'));
            // Redirect to edit page
            $url = $this->view->url(array('controller' => 'shift-booker', 'action' => 'manage-shifts'), null, true);
        }
        $this->_redirect($url, array('prependBase' => false));
    }

    /**
     * Show delete shift
     *
     * @return void
     */
    public function showDeleteShiftAction()
    {
        $this->view->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

        $defaultShiftId = $this->getRequest()->getParam('shiftId');
        $staffId        = $this->getRequest()->getParam('staffId');
        $userType       = $this->getRequest()->getParam('userType');
        $shiftMapper    = Application_Model_ShiftMapperFactory::getObject($userType);
        $shift          = $shiftMapper->getShift($staffId, $defaultShiftId);

        $userMapper = new Application_Model_UserMapper;
        $user = $userMapper->fetchById($staffId);

        $this->view->shiftId  = $shift['id'];
        $this->view->user     = $user;
        $this->view->userType = $userType;

        $output           = array();
        $output['status'] = 'ok';
        $output['html']   = $this->view->render('shift-booker/_deleteShift.phtml');
        echo json_encode($output);
    }

    /**
     * Delete shift admin
     *
     * @return void
     */
    public function deleteShiftAdminAction()
    {
        $this->view->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
        $userType    = $this->_request->getParam('userType');
        $shiftId     = $this->_request->getParam('shiftId');
        $shiftMapper = Application_Model_ShiftMapperFactory::getObject($userType);
        $result      = $shiftMapper->deleteById($shiftId);

        if ($result)
        {

            $this->_helper->FlashMessenger(array('warning' => 'Shift has been removed'));
        }
        else
        {
            $this->_helper->FlashMessenger(array('error' => 'There was an error deleting shift, please contact system administrator'));
        }
    }

    /**
     * Validate and process shift time updates
     *
     * @param int $shiftId
     * @param Zend_Form $form
     * @param array $formData
     * @param string $userType
     *
     * return void
     */
    protected function _processShiftForm($shiftId, $form, $formData, $userType)
    {
       $defaultShiftMapper = Application_Model_DefaultShiftMapperFactory::getObject($userType);

       if ($form->isValid($formData))
       {
            $startDayNumber = $formData['start_day'];
            $startTime      = $formData['start_time'];
            $endDayNumber   = $formData['end_day'];
            $endTime        = $formData['end_time'];

            // Check if the dates supplied are valid
            if (($startDayNumber) == $endDayNumber & ($startTime == $endTime))
            {
               $this->_helper->FlashMessenger(array('error' => 'Please enter valid start and end dates'));
            }
            else
            {
                // Get day name
                $startDay = date("l", mktime(0, 0, 0, 11, ($startDayNumber - 1), 2011));
                $endDay   = date("l", mktime(0, 0, 0, 11, ($endDayNumber - 1), 2011));

                $data               = array();
                $data['start_day_number'] = $startDayNumber;
                $data['start_day']        = $startDay;
                $data['start_time']       = $startTime;
                $data['end_day_number']   = $endDayNumber;
                $data['end_day']          = $endDay;
                $data['end_time']         = $endTime;

                if (null !== $shiftId)
                {
                    $defaultShiftMapper->update($data, 'id = ' . $shiftId);
                }
                else
                {
                    $this->_helper->FlashMessenger(array('notice' => 'Shift succesfully added'));
                    $shiftId = $defaultShiftMapper->insert($data);
                    $url     = $this->view->url(array('controller' => 'shift-booker', 'action' => 'manage-shifts'), null, true);
                    $this->_redirect($url, array('prependBase' => false));
                }

                $this->_helper->FlashMessenger(array('notice' => 'Shift updated'));
            }
        }
        else
        {
            $this->_helper->FlashMessenger(array('error' => 'There are validation errors'));
        }
    }

    protected function ajaxFetchShiftTimesAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $shiftDate = $this->_request->getParam('shift_date');
        if ('today' == $shiftDate)
        {
            $shiftDate = date('Y-m-d');
        }
        $userType  = $this->_request->getParam('user_type');
        $shiftMapper = Application_Model_DefaultShiftMapperFactory::getObject(Application_Model_UsersShiftMapper::TYPIST_SHIFT);
        if ($userType == Application_Model_UsersShiftMapper::PROOFREADER_SHIFT)
        {
            $shiftMapper = Application_Model_DefaultShiftMapperFactory::getObject(Application_Model_UsersShiftMapper::PROOFREADER_SHIFT);
        }

        // Get day number from date
        $dayNumber = date('N', strtotime($shiftDate));
        $shiftTimes = $shiftMapper->getShiftTimesForDropdown($dayNumber);
        echo json_encode($shiftTimes);
        exit();
    }
}

