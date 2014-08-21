<?php

/**
 * Test Emails Controller
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: TestEmailsController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */

/**
 * Test Emails Controller
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: TestEmailsController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */
class TestEmailsController extends Zend_Controller_Action
{
    const RECEIVER_EMAIL                 = 'jibril.salad@oneresult.co.uk';
    const EXISTING_AUDIO_JOB_ID          = 3;
    const EXISTING_JOB_ID                = 1;
    const EXISTING_JOB_CHANGE_REQUEST_ID = 1;
    const AUDIO_JOB_TYPIST_ID            = 2;
    const AUDIO_JOB_PROOFREADER_ID       = 1;
    const TRANSCRIPTION_FILE_ID          = 1;

    protected $_forgotPasswordResetEmailModel;
    protected $_forgotPasswordDetailsModel;
    protected $_newUserEmailModel;
    protected $_groupEmailModel;
    protected $_reportEmailModel;
    protected $_updateUserEmailModel;
    protected $_existingEmailsModel;

    protected $_httpHost;
    protected $_appUrl;
    protected $_user;

    /**
     * Initialize object
     *
     * @return void
     */
    public function init()
    {
        $this->_forgotPasswordResetEmailModel = new App_Mail_PasswordResetEmail();
        $this->_forgotPasswordDetailsModel = new App_Mail_PasswordDetailsEmail();
        $this->_newUserEmailModel = new App_Mail_NewUserEmail();
        $this->_groupEmailModel = new App_Mail_GroupEmail();
        $this->_reportEmailModel = new App_Mail_ReportEmail();
        $this->_updateUserEmailModel = new App_Mail_UpdateUserEmail();

        $this->_existingEmailsModel = $this->_helper->getHelper('email');

        // Dynamically get the components of the application URL
        $this->_httpHost = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
        $this->_appUrl = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost() . $this->getRequest()->getBaseUrl();

        $this->_user = array(
            'id' => 92,
            'acl_role_id' => 1,
            'acl_group_id' => 1,
            'name' => 'test',
            'email' => self::RECEIVER_EMAIL,
            'username' => 'tester',
            'salt' => '6260a8a5528fd7fa1b45e81bf3120830',
            'password' => '76fd65f39ad4bbeb0cf8eb885ee0f635',
            'pass_phrase' => '309b4e0a02f773510968acd15b578838',
            'password_reset_key' => '',
            'email_alternative' => '',
            'landline' => '',
            'mobile' => '',
            'active' => 1,
            'comments' => '',
            'created_date' => '0000-00-00 00:00:00',
            'created_user_id' => 0
        );
    }

    /**
     * Constructor
     *
     * @return void
     */
    public function indexAction()
    {
        echo 'Clearing Emails in Cache folder <br /><br />';
        $this->_clearCacheFolder();

        $emails = array(
            'Password Reset'                                  => 'passwordResetEmail',
            'New Password'                                    => 'newPasswordEmail',
            'User Update Email'                               => 'sendUserUpdateNotification',
            'New User Email'                                  => 'sendUserCreateNotification',

            'Report Email'                                    => 'reportEmail',
            'Group Email'                                     => 'sendGroupEmail',

            'Audio Job Received Email'                        => 'sendAudioJobReceivedEmail',
            'Support File Received Email'                     => 'sendSupportFileReceivedEmail',

            'Audio Job Link Received Email'                   => 'sendAudioJobLinkReceivedEmail',
            'Audio Job Approved Email'                        => 'sendAudioJobApprovedEmail',
            'Audio Job Assigned To Typist Email'              => 'sendAudioJobAssignedToTypistEmail',
            'Audio Job Assigned To Proofreader Email'         => 'sendAudioJobAssignedToProofreaderEmail',
            'Audio Job Typist Transcript Uploaded Email'      => 'sendAudioJobTypistTranscriptUploadedEmail',
            'Audio Job Proofreader Transcript Uploaded Email' => 'sendAudioJobProofreaderTranscriptUploadedEmail',
            'Audio Job Cancelled Email'                       => 'sendAudioJobCancelledEmail',
            'Audio Job Typist Cancelled Email'                => 'sendAudioJobTypistCancelledEmail',
            'Audio Job Proofreader Cancelled Email'           => 'sendAudioJobProofreaderCancelledEmail',
            'Audio Job Typist Panic Email'                    => 'sendAudioJobTypistPanicEmail',
            'Audio Job Proofreader Panic Email'               => 'sendAudioJobProofreaderPanicEmail',
            'Audio Job Returned To Client Email'              => 'sendAudioJobReturnedToClientEmail',
            'Job Completed Email'                             => 'sendJobCompletedEmail',
            'Job Change Request Email'                        => 'sendJobChangeRequestEmail',

            'Quote Email'                                       => 'sendQuoteEmail',
            'Shift Reminder Email'                              => 'sendShiftReminderEmail'
        );

        if (!empty($emails))
        {
            $counter = 1;
            foreach ($emails as $label => $func)
            {
                echo '(' . $counter . ') Sending email: ' . $label . '<br />';
                //$func = '_' . $label;
                if (true == $this->$func())
                {
                    echo 'sending succesful <br />';
                }
                else
                {
                    echo 'sending failed <br />';
                }
                $counter++;
            }
        }

        exit('END');
    }

    /**
     * Clear cache directory
     *
     * @return void
     */
    protected function _clearCacheFolder()
    {
        $cacheUrl = realpath(APPLICATION_PATH . '/../cache/');
        if(!$dh = @opendir($cacheUrl)) return;
        while (false !== ($obj = readdir($dh))) {
            if($obj=='.' || $obj=='..') continue;
            $fileName = $cacheUrl.'/'.$obj;
            if (strpos($fileName, "email"))
            {
                if (!@unlink($fileName))
                {
                    echo 'Unable to remove ' . $fileName . ' <br />';
                }
            }
        }

        closedir($dh);
    }


    public function sendQuoteEmail()
    {
        $data['transcription_type_id'] = 1;
        $data['turnaround_time_id']    = 1;
        $data['type']                  = 'total-hours';
        $data['total-time-minutes']    = 5;
        $data['what']                  = 1;

                $viewData = array();
                $prices   = new Application_Model_TranscriptionPriceMapper;
                $price    = $prices->getPrice($data['transcription_type_id'], $data['turnaround_time_id']);

                $transcriptionMapper = new Application_Model_TranscriptionTypeMapper;
                $turnAroundMapper    = new Application_Model_TurnaroundTimeMapper;

                $transcription = $transcriptionMapper->fetchById($data['transcription_type_id']);
                $turnAround    = $turnAroundMapper->fetchById($data['turnaround_time_id']);

                if ('total-hours' === $data['type'])
                {
                    if (empty($data['total-time-hours']))
                    {
                        $hours = 0;
                    }
                    else
                    {
                        $hours = $data['total-time-hours'];
                    }
                    if (empty($data['total-time-minutes']))
                    {
                        $minutes = 0;
                    }
                    else
                    {
                        $minutes = $data['total-time-minutes'];
                    }
                    $length     = (($hours * 60) + $minutes) * 60;
                    $totalPrice = $this->_calculatePrice($length, $price);

                    $viewData['totalTime'] = $hours . ' hrs ' . $minutes . ' mins';
                }
                elseif ('ind-files' === $data['type'])
                {
                    $totalHours   = 0;
                    $totalMinutes = 0;
                    $totalPrice   = 0;
                    $files        = array();

                    foreach ($data['ind-time-hours'] as $key => $hours)
                    {
                        if (empty($hours))
                        {
                            $totalHours += 0;
                        }
                        else
                        {
                            $totalHours += $hours;
                        }
                        $minutes = $data['ind-time-minutes'][$key];
                        if (empty($minutes))
                        {
                            $totalMinutes += 0;
                        }
                        else
                        {
                            $totalMinutes += $minutes;
                        }
                        $fileLength     = (($hours * 60) + $minutes) * 60;
                        $filePrice  = $this->_calculatePrice($fileLength, $price);
                        $totalPrice += $filePrice;
                        $files[$key] = array(
                            'duration'   => $hours . ' hrs ' . $minutes . ' mins',
                            'price'      => $filePrice,
                            'totalPrice' => $filePrice * 1.2
                        );
                    }

                    $length            = (($totalHours * 60) + $totalMinutes) * 60;
                    $viewData['files'] = $files;
                }

                if ('1' === $data['what'])
                {
                    $viewData['what'] = $data['what_other'];
                }
                else
                {
                    $viewData['what'] = $data['what'];
                }

                if (!empty($data['discount']) && $data['discount'] <= 100)
                {

                    $totalPrice = $totalPrice - ($totalPrice * $data['discount']/100);
                }

        $quoteEmail = new App_Mail_QuoteEmail;
        $quoteEmail->setView($this->view)
            ->setViewData($viewData)
            ->addReceiver(self::RECEIVER_EMAIL);

        if ($quoteEmail->sendMail())
        {
            return true;
        }

        return false;
    }

    /**
     * Calculate total price with 2 minute grace period
     *
     * @param int   $length File(s) length
     * @param float $price  Price
     *
     * @return float
     */
    protected function _calculatePrice($length, $price)
    {
        $grace = ($length / 60) % (15);
        if (($length / 60) > 15 && $grace <= 2)
        {
            $totalPrice = floor(($length / 60) / 15) * $price;
        }
        else
        {
            $totalPrice = ceil(($length / 60) / 15) * $price;
        }
        return $totalPrice;
    }

    public function sendShiftReminderEmail()
    {
        $usersShiftMapper = new Application_Model_UsersShiftMapper();
        $startDate        = date('Y-m-d');
        $fortnightDate    = $date = date("Y-m-d", strtotime("{$startDate} +14 days"));
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
            return true;
        }
        else
        {
            return false;
        }
    }

    public function sendAudioJobReceivedEmail()
    {
        $options['uploadKey'] = '1-87-1355139152';
        return $this->_sendExistingEmail('audioJobReceived', $options);
    }

    public function sendSupportFileReceivedEmail()
    {
        $options['uploadKey'] = '1-87-1355139152';
        return $this->_sendExistingEmail('supportFileReceived', $options);
    }

    public function sendAudioJobLinkReceivedEmail()
    {
        $options['id'] = self::EXISTING_AUDIO_JOB_ID;
        return $this->_sendExistingEmail('audioJobLinkReceived', $options);
    }

    public function sendAudioJobApprovedEmail()
    {
        $options['id'] = array(self::EXISTING_AUDIO_JOB_ID);
        return $this->_sendExistingEmail('audioJobApproved', $options);
    }

    public function sendAudioJobAssignedToTypistEmail()
    {
        $options['id'] = self::AUDIO_JOB_TYPIST_ID;
        return $this->_sendExistingEmail('audioJobAssignedToTypist', $options);
    }

    public function sendAudioJobAssignedToProofreaderEmail()
    {
        $options['id'] = self::AUDIO_JOB_PROOFREADER_ID;
        return $this->_sendExistingEmail('audioJobAssignedToProofreader', $options);
    }

    public function sendAudioJobTypistTranscriptUploadedEmail()
    {
        $options['id'] = self::TRANSCRIPTION_FILE_ID;
        return $this->_sendExistingEmail('audioJobTypistTranscriptUploaded', $options);
    }

    public function sendAudioJobProofreaderTranscriptUploadedEmail()
    {
        $options['id'] = self::TRANSCRIPTION_FILE_ID;
        return $this->_sendExistingEmail('audioJobProofreaderTranscriptUploaded', $options);
    }

    public function sendAudioJobCancelledEmail()
    {
        $options['id'] = self::EXISTING_AUDIO_JOB_ID;
        return $this->_sendExistingEmail('audioJobCancelled', $options);
    }

    public function sendAudioJobTypistCancelledEmail()
    {
        $options['id'] = self::AUDIO_JOB_TYPIST_ID;
        return $this->_sendExistingEmail('audioJobTypistCancelled', $options);
    }

    public function sendAudioJobProofreaderCancelledEmail()
    {
        $options['id'] = self::AUDIO_JOB_PROOFREADER_ID;
        return $this->_sendExistingEmail('audioJobProofreaderCancelled', $options);
    }

    public function sendAudioJobTypistPanicEmail()
    {
        $options['id'] = self::AUDIO_JOB_TYPIST_ID;
        return $this->_sendExistingEmail('audioJobTypistPanic', $options);
    }

    public function sendAudioJobProofreaderPanicEmail()
    {
        $options['id'] = self::AUDIO_JOB_PROOFREADER_ID;
        return $this->_sendExistingEmail('audioJobProofreaderPanic', $options);
    }

    public function sendAudioJobReturnedToClientEmail()
    {
        $options['id'] = self::EXISTING_JOB_ID;
        return $this->_sendExistingEmail('audioJobReturnedToClient', $options);
    }

    public function sendJobCompletedEmail()
    {
        $options['id'] = self::EXISTING_JOB_ID;
        return $this->_sendExistingEmail('jobCompleted', $options);
    }

    public function sendJobChangeRequestEmail()
    {
        $options['id'] = self::EXISTING_JOB_CHANGE_REQUEST_ID;
        return $this->_sendExistingEmail('jobChangeRequest', $options);
    }

    protected function _sendExistingEmail($emailType, $options)
    {
        $view->appUrl         = $this->_appUrl;
        $options['emailType'] = $emailType;
        try
        {
            $this->_existingEmailsModel->send($this->view, $options);
        } catch (Exception $e)
        {
            return false;
        }

        return true;
    }

    public function passwordResetEmail()
    {
        // Password reset url
        $passwordResetKey = 'RANDOMPASSWORDRESETKEY';
        $actionUrl = $this->view->url(array('module' => 'default', 'controller' => 'amnesia', 'action' => 'reset-password', 'key' => $passwordResetKey, 'login' => $this->_user['email']), null, true);
        $passwordResetUrl = $this->_httpHost . $actionUrl;

        $config = Zend_Registry::get('config');

        // Send Password Reset Email
        $this->_forgotPasswordResetEmailModel->setSenderWebsite($this->_appUrl)
            ->setCustomerUserName($this->_user['username'])
            ->setReceiver($this->_user['email'])
            ->setView($this->view)
            ->setPasswordResetUrl($passwordResetUrl);

        if ($this->_forgotPasswordResetEmailModel->sendMail())
        {
            return true;
        }

        return false;
    }

    public function newPasswordEmail()
    {
        $config = Zend_Registry::get('config');

        //Send Password Reset Email
        $this->_forgotPasswordDetailsModel->setSenderWebsite($this->_appUrl)
            ->setCustomerUserName($this->_user['username'])
            ->setReceiver($this->_user['email'])
            ->setView($this->view)
            ->setPassword('SAMPLEPASSWORD');


        if ($this->_forgotPasswordDetailsModel->sendMail())
        {
            return true;
        }

        return false;
    }

    /**
     * Send group email
     *
     * @param array $groupList the list of people to send the email to
     *
     * @return void
     */
    protected function sendGroupEmail()
    {
        $groupList = array('receiver1@email.com', 'receiver2@email.com');

        // Send email
        if (!empty($groupList))
        {
            $groupMail = new App_Mail_GroupEmail();
            $groupMail->setView($this->view);

            // Bcc all users
            if (!empty($groupList))
            {
                foreach ($groupList as $email)
                {
                    $groupMail->addReceiver($email);
                }
            }

            $groupMail->setSubject('Testing Group Email');
            $groupMail->setMessageBody('this is a test message by TestEmailsController');

            if ($groupMail->sendMail())
            {
                return true;
            }

            return false;
        }

        return false;
    }

    public function reportEmail()
    {
        // Generate report url for email
        $reportRelativeUrl = $this->view->url(array('controller' => 'report', 'action' => 'view', 'id' => 1), null, true);
        $reportUrl = $this->_appUrl . '/' . $reportRelativeUrl;

        $userMapper = new Application_Model_UserMapper();
        $jobMapper = new Application_Model_JobMapper();

        $reportEmail = new App_Mail_ReportEmail();
        $reportEmail->setView($this->view);
        $reportEmail->setReceiver($this->_user['email'])
            ->setReportId(1)
            ->setProjectFileName('Test Report')
            ->setAudioJobFilename('thisfiledoesnotexist.mpg')
            ->setSiteUrl($this->_appUrl)
            ->setReportUrl($reportUrl);

        if ($reportEmail->sendMail())
        {
            return true;
        }

        return false;
    }

    /**
     * Send email to user that their account is updated
     *
     * @return void
     */
    public function sendUserUpdateNotification()
    {
        $userMail = $this->_setUserNotificationDefaults($this->_updateUserEmailModel);
        return $userMail->sendMail();
    }

    /**
     * Send newly created login details to user
     *
     * @return void
     */
    public function sendUserCreateNotification()
    {
        $userMail = $this->_setUserNotificationDefaults($this->_newUserEmailModel);
        $userMail->setPassword('TESTPASSWORD')
                 ->sendMail();
    }

    /**
     * Sets user email notification defaults shared by update and create user emails
     *
     * @param App_Mail_Mail $userMail the user email obj
     *
     * @return App_Mail_Mail
     */
    protected function _setUserNotificationDefaults($userMail)
    {
        $userMapper = new Application_Model_UserMapper();
        $userMail->setName($this->_user['name'])
                 ->setUsername($this->_user['username'])
                 ->setReceiver($this->_user['email'])
                 ->setSiteUrl($this->_appUrl)
                 ->setView($this->view);
        return $userMail;
    }

}
