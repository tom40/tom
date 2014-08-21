<?php

/**
 * User Shift Booker Controller
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    Application
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: UserShiftBookerController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */

/**
 * User Shift Booker Controller
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    Application
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: UserShiftBookerController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */
class UserShiftBookerController extends App_Controller_Action
{
    const BOOKING_ON_HOLIDAY_MESSAGE       = 'You have been marked as on holiday for the selected date';
    const BOOKING_SUCCESS_MESSAGE          = 'One or more shifts has been updated';
    const BOOKING_VALIDATION_ERROR_MESSAGE = 'There are validation errors';
    const BOOKING_DUE_ERROR_MESSAGE        = 'Bookings due in the next 24 hours cannot be changed';

    protected $_userType;
    protected $_shiftMapper;
    protected $_defaultShiftMapper;
    protected $_currentUserId;

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        $this->flashMessenger = $this->_helper->flashMessenger;
        parent::init();
        if ( Zend_Auth::getInstance()->hasIdentity() ) {
            $currentUserId = null;
            // If the current user is an Admin user check if userid is passed in
            if ($this->isAdminUser())
            {
                $selectedUserId = $this->_request->getParam('userid');
                if (!empty($selectedUserId))
                {
                    $currentUserId = $selectedUserId;
                }
            }
            else
            {
                $currentUserId = Zend_Auth::getInstance()->getIdentity()->id;
            }

            $this->_currentUserId      = $currentUserId;
            $this->view->currentUserId = $currentUserId;
        }

    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->shiftbookerForm = new Application_Form_UserShiftBooker();
        $this->_setUserType();
    }

    /**
     * Admin action
     *
     * @return void
     */
    public function adminAction()
    {
        $this->view->shiftbookerForm = new Application_Form_UserShiftBooker();
        $userType                    = $this->_request->getParam('usertype');
        $dualUserType                = $this->_request->getParam('dualusertype');
        if ($dualUserType == true)
        {
            // User is a typist and proofreader
            $this->view->dualUserTypeSelect = true;
        }

        $userMapper = new Application_Model_UserMapper();
        $userList   = $userMapper->getAllTypistsAndProofreaders();
        if (!empty($userType))
        {
            $this->_userType      = $userType;
            $this->view->userType = $userType;
        }

        $this->view->userList = $userList;
    }

    /**
     * Sets the correct model for the current user type
     *
     * @return void
     */
    protected function _setUserType()
    {
        if (empty($this->_userType))
        {
           $this->_userType  = $this->_request->getParam('usertype');
           $typistModel      = new Application_Model_TypistMapper();
           $proofreaderModel = new Application_Model_ProofreaderMapper();

           $typistUser      = null;
           $proofreaderUser = null;
           if (!empty($this->_currentUserId))
           {
               $typistUser      = $typistModel->fetchByUserId($this->_currentUserId);
               $proofreaderUser = $proofreaderModel->fetchByUserId($this->_currentUserId);
           }

           if (empty($this->_userType))
           {
            if (!empty($typistUser))
                {
                    $this->_userType = Application_Form_UserShiftBooker::TYPIST_SHIFT;
                }
                else
                {
                    if (!empty($proofreaderUser))
                    {
                        $this->_userType = Application_Form_UserShiftBooker::PROOFREADER_SHIFT;
                    }
                }
           }

           // Handle if the user is both typist and proofreader
           if (!empty($typistUser) && !empty($proofreaderUser))
           {
                $this->view->dualUserTypeSelect = true;
           }

           $this->_shiftMapper        = Application_Model_ShiftMapperFactory::getObject($this->_userType);
           $this->_defaultShiftMapper = Application_Model_DefaultShiftMapperFactory::getObject($this->_userType);
        }

        $this->view->userType = $this->_userType;
    }

    /**
     * Sets the correct model for the current user type
     * used for Admin Action only currently
     *
     * @return void
     */
    protected function _getUserType($userId)
    {
        $typistModel      = new Application_Model_TypistMapper();
        $proofreaderModel = new Application_Model_ProofreaderMapper();
        $typistUser       = $typistModel->fetchByUserId($this->_currentUserId);
        $proofreaderUser  = $proofreaderModel->fetchByUserId($this->_currentUserId);

        // Handle if the user is both typist and proofreader
        if (!empty($typistUser) && !empty($proofreaderUser))
        {
            return 'both';
        }
        elseif (!empty($typistUser))
        {
            return Application_Form_UserShiftBooker::TYPIST_SHIFT;
        }
        elseif (!empty($proofreaderUser))
        {
            return Application_Form_UserShiftBooker::PROOFREADER_SHIFT;
        }

        echo null;
    }

    /**
     * Fetches the user type of the user provided
     *
     * @return void
     */
    public function ajaxFetchUserTypeAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $userId = $this->_request->getParam('userid');
        if (!empty($userId))
        {
            echo $this->_getUserType($userId);
        }

        echo '';
    }

    /**
     * Fetches formatted shift data  in JSON format
     *
     * @return void
     */
    public function ajaxFetchShiftsAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $shiftData = array();
        if (!empty($this->_currentUserId))
        {
            $this->_setUserType();

            $shifts = $this->_shiftMapper->fetchUserShifts($this->_currentUserId);

            if (!empty($shifts))
            {
                foreach($shifts as $shift)
                {
                    $shiftInfo        = array();
                    $shiftMapperModel = $this->_shiftMapper;

                    if ($shift['status'] == Application_Model_UsersShiftMapper::HOLIDAY_SHIFT_STATUS)
                    {
                        $shiftInfo['title']     = $shift['status'];
                        $shiftInfo['start']     = $shift['shift_date'];
                        $shiftInfo['end']       = $shift['shift_date'];
                        $shiftInfo['allDay']    = true;
                        $shiftInfo['className'] = 'holiday-bg';
                    }
                    else
                    {
                        $shiftDate              = $shift['shift_date'];
                        $shiftInfo['title']     = $shift['status'];
                        $shiftInfo['start']     = $shiftDate . ' ' . $shift['start_time'];

                        $shiftInfo['allDay']    = false;
                        $shiftInfo['className'] = 'shift-booked-bg';

                        // Calculate the date of the end day
                        if ($shift['start_day'] == $shift['end_day'])
                        {
                            $endDate = $shiftDate;
                        }
                        else
                        {
                            $endDay    = strtolower($shift['end_day']);
                            $endDate   = date('Y-m-d', strtotime("$shiftDate next {$endDay}"));
                        }

                        $shiftInfo['end'] = $endDate . ' ' . $shift['end_time'];
                    }

                    $shiftData[] = $shiftInfo;

                }
            }
        }
        echo json_encode($shiftData);
    }

    /**
     * Book a shift action
     *
     * @return void
     */
    public function ajaxCreateShiftAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $template     = 'user-shift-booker/ajax-create-shift.phtml';
        $output       = array();
        $outputStatus = 'ok';
        $hasShifts    = true;

        $this->_setUserType();
        $shiftDate = $this->_request->getParam('date');

        // Shift is in the past do not allow further operations on it
        $todaysDate         = date('Y-m-d');
        $shiftDateInThePast = false;
        if ($todaysDate > $shiftDate)
        {
            $shiftDateInThePast = true;
        }

        if (!$shiftDateInThePast)
        {
            $shiftDay = $this->_defaultShiftMapper->fetchAvailableShiftDays($shiftDate);
            if (empty($shiftDay))
            {
                $hasShifts = false;
            }

            $shiftbookerForm = new Application_Form_UserShiftBooker();
            $shiftbookerForm->populateShiftTime($shiftDate, $this->_userType);
            if ($this->getRequest()->isPost())
            {
                $formData = $this->getRequest()->getPost();

                // If holiday is selected shift time is no longer required
                $isOnHoliday = ( isset($formData['on_holiday']) && (1 == $formData['on_holiday']) ) ? true : false;
                if ($isOnHoliday)
                {
                    $shiftbookerForm->getElement('shift_time')->setRequired(false);
                }

                if ($shiftbookerForm->isValid($formData))
                {
                    $currentShifts  = $this->_shiftMapper->fetchShiftTimesByDate($this->_currentUserId, $shiftDate);
                    $selectedShifts = isset($formData['shift_time']) ? $formData['shift_time'] : array();
                    $hasDueBookings = false;
                    $changedShifts  = array();
                    $excludeShifts  = array();

                    if (!empty($currentShifts))
                    {
                        $errors = array();
                        foreach($currentShifts as $shift)
                        {
                            if (!in_array($shift['shift_id'], $selectedShifts))
                            {
                                if (false == $shift['on_holiday'])
                                {
                                    $changedShifts[] = $shift['shift_id'];

                                    if (!$this->isAdminUser())
                                    {
                                        // check if in the next 24 hours
                                        if ($this->_isDueInNext24Hours($shiftDate, $shift['start_time']))
                                        {
                                            $errors[]        = 'The shift ' . $this->view->formatTime($shift['start_time']) . ' - ' . $this->view->formatTime($shift['end_time']) . ' is due in 24 hours and cannot be changed';
                                            $hasDueBookings  = true;
                                            $outputStatus    = 'error';
                                            $excludeShifts[] = $shift['shift_id'];
                                        }
                                    }
                                }
                            }
                        }

                        if ($hasDueBookings)
                        {
                            $this->flashMessenger->addMessage(array('error' => implode('<br /><br />', $errors)));
                        }
                    }
                        // Clear existing shifts for this date
                        $this->_shiftMapper->clearDateShifts($this->_currentUserId, $shiftDate, $excludeShifts);

                        // Add new shift data
                        if ($isOnHoliday)
                        {
                            if (!$hasDueBookings)
                            {
                                $this->view->isOnHoliday = true;
                                $this->_bookShift($shiftDate, null, $this->_shiftMapper, Application_Model_UsersShiftMapper::HOLIDAY_SHIFT_STATUS);
                                $this->flashMessenger->addMessage(array('notice' => self::BOOKING_ON_HOLIDAY_MESSAGE));
                            }
                            else
                            {
                                $formData['on_holiday'] = 0;
                            }
                        }
                        else
                        {
                            if (!empty($selectedShifts))
                            {
                                foreach ($selectedShifts as $shiftId)
                                {
                                    $this->_bookShift($shiftDate, $shiftId, $this->_shiftMapper, Application_Model_UsersShiftMapper::BOOKED_SHIFT_STATUS);
                                }
                                $this->flashMessenger->addMessage(array('notice' => self::BOOKING_SUCCESS_MESSAGE));
                            }
                        }
                }
                else
                {
                    $this->flashMessenger->addMessage(array('error' => self::BOOKING_VALIDATION_ERROR_MESSAGE));
                }
                $shiftbookerForm->populate($formData);
            }
            else
            {
                $shiftData = $this->_shiftMapper->fetchDateShifts($this->_currentUserId, $shiftDate);
                $result    = $this->_getFormattedShiftData($shiftData);
                $shiftbookerForm->populate($result);
            }

            $this->view->userType      = $this->_userType;
            $this->view->shiftDate     = $shiftDate;
            $this->view->form          = $shiftbookerForm;
            $this->view->hasShifts     = $hasShifts;
        }

        $this->view->shiftDateInThePast = $shiftDateInThePast;
        $outputHtml                     = $this->view->render($template);
        $output['html']                 = $outputHtml;
        $output['status']               = $outputStatus;
        echo json_encode($output);
    }

    /**
     * Check if a given user is an admin user
     * 
     * @return void
     */
    public function isAdminUser()
    {
        if ( Zend_Auth::getInstance()->hasIdentity() ) {
            $currentUserId = null;
            if ($this->_acl->isAdmin())
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if shift is due in the next 24 hours
     *
     * @param type $shiftDate
     * @param string $startTime
     * @return type
     */
    protected function _isDueInNext24Hours($shiftDate, $startTime)
    {
        if ('00:00:00' == $startTime)
        {
            $startTime = '23:59:59';
        }

        $shiftTime       = $shiftDate . ' ' . $startTime;
        $selectedTime    = strtotime($shiftTime);
        $todaysDateTime  = strtotime(date('Y-m-d H:i:s'));
        $hoursDifference = floor(($selectedTime - $todaysDateTime) / 3600);

        if ($hoursDifference < 24)
        {
            return true;
        }

        return false;
    }

    /**
     * Books a shift
     *
     * @param string $shiftDate
     * @param int $shiftId
     * @param Application_Model_TypistsShiftMapper|Application_Model_ProofreadersShiftMapper $shiftMapperModel
     * @param string $shiftStatus
     *
     * @return void
     */
    protected function _bookShift($shiftDate, $shiftId, $shiftMapperModel, $shiftStatus)
    {
        $data               = array();
        $data['shift_id']   = $shiftId;
        $data['shift_date'] = $shiftDate;
        $data['user_id']    = $this->_currentUserId;
        $data['status']     = $shiftStatus;
        $shiftMapperModel->insert($data);
    }

    /**
     * Formats given shift data for populating the shift booker form
     *
     * @param array $shiftData
     *
     * @return array
     */
    protected function _getFormattedShiftData($shiftData)
    {
        $formattedShiftData = array();
        if (!empty($shiftData))
        {
            foreach ($shiftData as $data)
            {
                if (1 == $data['on_holiday'])
                {
                    $formattedShiftData['on_holiday'] = 1;
                    $this->view->isOnHoliday          = true;
                    continue;
                }
                else
                {
                    $formattedShiftData['shift_time'][] = $data['shift_time'];
                }
            }
        }

        return $formattedShiftData;
    }
}

