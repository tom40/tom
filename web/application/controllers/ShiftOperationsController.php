<?php

/**
 * Shift Operations Controller used from
 * the command line to execute tasks
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    Application
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: ShiftOperationsController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */

/**
 * Shift Operations Controller
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    Application
 * @subpackage Controllers
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: ShiftOperationsController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */
class ShiftOperationsController extends Zend_Controller_Action
{
    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        // Disable the normal layout
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Sends reminders to typists/proofreaders who have not booked a shift in the next 2 weeks
     *
     * @return void
     */
    public function noShiftRemindersAction()
    {
        $usersShiftMapper = new Application_Model_UsersShiftMapper();
        $startDate        = date('Y-m-d');
        $fortnightDate    = $date = date("Y-m-d", strtotime("{$startDate} +7 days"));
        $reminderList     = $usersShiftMapper->getUsersWithNoShift($startDate, $fortnightDate);

        // Send email notification to the users
        $mail = new App_Mail_UserShiftReminderEmail();
        foreach ($reminderList as $user)
        {
            $mail->addBcc($user['email']);
        }

        $mail->setView($this->view);
        if ($mail->sendMail())
        {
            echo 'Shift reminder email succesfull sent';
        }
        else
        {
            echo 'Unable to send shift reminder email';
        }

        exit();
    }
}

