<?php

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';


/**
 * Email notification
 *
 * App_Controller_Action_Helper_Email provides email notification support to a controller.
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @package    Controller
 * @subpackage Controller_Action
 * @copyright Copyright (c) 2012 Take Note
 */
class App_Controller_Action_Helper_Email extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Constructor
     *
     * @return self
     **/
    function __construct()
    {
    }

    /**
     * Returns the Acl Plugin object
     *
     * @return App_Controller_Plugin_Acl
     **/
    public function send($view, $options)
    {
    	// check global config setting allows emails to be sent
    	$config = Zend_Registry::get('config');

        // Dynamically get the components of the application URL
        $appUrl = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost() . $this->getRequest()->getBaseUrl();

    	if ($config->app->email->notifications->active != 1) {
    		return false;
    	}

    	$mail = new App_Mail();

        $forceReceiver = '';
        $fromSet       = false;

    	$view->hasTopMenu = false;
    	switch ($options['emailType']) {
    		case 'audioJobReceived':
    			$audioJobMapper = new Application_Model_AudioJobMapper();
    			$data = $audioJobMapper->fetchByUploadKey($options['uploadKey']);
    			$view->data = $data;

                $options['job_id'] = $data[0]['job_id'];
    			$subject = 'Take Note: New audio file(s) received';
    			break;
			case 'supportFileReceived':
    			$supportFileMapper = new Application_Model_SupportFileMapper();
    			$data = $supportFileMapper->fetchByUploadKey($options['uploadKey']);
    			$view->data = $data;
                $options['job_id'] = $data[0]['job_id'];

    			$subject = 'Take Note: New support document(s) received';
    			break;
			case 'audioJobLinkReceived':
    			$audioJobMapper = new Application_Model_AudioJobMapper();
    			$data = $audioJobMapper->fetchById($options['id']);
    			$view->data = $data;
                $options['job_id'] = $data['job_id'];

    			$subject = 'Audio link received';
    			break;

			case 'audioJobApproved':
				$audioJobMapper = new Application_Model_AudioJobMapper();
    			$data = $audioJobMapper->fetchByIdArray($options['id']);
    			$view->data = $data;
                $options['job_id'] = $data[0]['job_id'];

    			$subject = 'Your files have now been approved';
    			break;
			case 'audioJobAssignedToTypist':
				$audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
    			$data = $audioJobTypistMapper->fetchById($options['id']);
    			$view->data = $data;
                $options['user_id'] = $data['user_id'];
                // Dynamically get the components of the application URL
                $httpHost     = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
                $appUrl       = $httpHost . $this->getRequest()->getBaseUrl();
                $view->appUrl = $appUrl;

    			$subject = 'A new audio file has been assigned to you';

                $mail->addBcc($config->app->email->transcriptsEmail);
    			break;

			case 'audioJobAssignedToProofreader':
    			$audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
    			$data = $audioJobProofreaderMapper->fetchById($options['id']);
    			$view->data = $data;
                $options['user_id'] = $data['user_id'];

                $httpHost     = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
                $appUrl       = $httpHost . $this->getRequest()->getBaseUrl();
                $view->appUrl = $appUrl;

    			$subject = 'A new file has been assigned to you for proofreading';
                $mail->addBcc($config->app->email->transcriptsEmail);
    			break;

           case 'audioJobProofreaderDeadlineChanges':
    			$audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
    			$data = $audioJobProofreaderMapper->fetchByIdWithTranscriptionType($options['id']);
    			$view->data = $data;
                $options['user_id'] = $data['user_id'];

                $httpHost     = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
                $appUrl       = $httpHost . $this->getRequest()->getBaseUrl();
                $view->appUrl = $appUrl;

    			$subject = 'A change has been made to the following booking: ' . $data['file_name'] . '. Please read before continuing.';
                $mail->addBcc($config->app->email->transcriptsEmail);
    			break;

			case 'audioJobTypistDeadlineChanges':
				$audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
    			$data = $audioJobTypistMapper->fetchByIdWithTranscriptionType($options['id']);
    			$view->data = $data;
                $options['user_id'] = $data['user_id'];
                // Dynamically get the components of the application URL
                $httpHost     = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
                $appUrl       = $httpHost . $this->getRequest()->getBaseUrl();
                $view->appUrl = $appUrl;

    			$subject = 'A change has been made to the following booking: ' . $data['file_name'] . '. Please read before continuing.';

                $mail->addBcc($config->app->email->transcriptsEmail);
    			break;

    		case 'audioJobTypistTranscriptUploaded':
    			$transcriptionFileMapper = new Application_Model_TranscriptionFileMapper();

                $data       = $transcriptionFileMapper->fetchById($options['id']);
                $mail       = $this->_attachTranscription($mail, $data);
    			$view->data = $data;
                
                $options['user_id'] = $data['user_id'];
    			$subject = 'Transcript received';
                break;
    		case 'audioJobProofreaderTranscriptUploaded':
    			$transcriptionFileMapper = new Application_Model_TranscriptionFileMapper();

                $data       = $transcriptionFileMapper->fetchById($options['id']);
                $mail       = $this->_attachTranscription($mail, $data);
    			$view->data = $data;

                $options['user_id'] = $data['user_id'];
    			$subject = 'Proofread transcript received';
				break;

			case 'audioJobCancelled':
                // Currently unused
    			$audioJobMapper = new Application_Model_AudioJobMapper();
                $data = $audioJobMapper->fetchByIdArray($options['id']);
    			$view->data = $data;
                $options['job_id'] = $data[0]['job_id'];
    			$subject = 'Take Note: URGENT - Change to your booking';
    			break;

			case 'audioJobTypistCancelled':
    			$audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
    			$data = $audioJobTypistMapper->fetchById($options['id']);
    			$view->data = $data;
                $options['user_id'] = $data['user_id'];

    			$subject = 'URGENT - Please stop working on the following transcript: ' . $data['file_name'] . '. Booking cancelled.';
    			break;

			case 'audioJobProofreaderCancelled':
    			$audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
    			$data = $audioJobProofreaderMapper->fetchById($options['id']);
    			$view->data = $data;
                $options['user_id'] = $data['user_id'];

    			$subject = 'URGENT! Proofreading booking change/cancellation';
    			break;

    		case 'audioJobTypistPanic':
    			$audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
    			$data = $audioJobTypistMapper->fetchById($options['id']);
    			$view->data = $data;
                $options['user_id'] = $data['user_id'];
                $forceReceiver      = $config->app->email->bookingEmail;

                $userMapper = new Application_Model_UserMapper();
                $user       = $userMapper->fetchRow( 'id = ' .$data['user_id'] );

                $mail->setReplyTo( $user->email );

    			$subject = 'A typist has pressed the panic button!';
				break;

			case 'audioJobProofreaderPanic':
				$audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
				$data = $audioJobProofreaderMapper->fetchById($options['id']);
				$view->data = $data;
                $options['user_id'] = $data['user_id'];
                $forceReceiver      = $config->app->email->bookingEmail;

                $userMapper = new Application_Model_UserMapper();
                $user       = $userMapper->fetchRow( 'id = ' . $data['user_id'] );

                $mail->setReplyTo( $user->email );

				$subject = 'A proofreader has pressed the panic button!';
				break;

    		case 'audioJobReturnedToClient':

    			$includeAttachment = true;
    			$audioJobMapper    = new Application_Model_AudioJobMapper();
    			$jobData           = $audioJobMapper->fetchJobId($options['id']);
                $jobId             = $jobData['job_id'];
                $options['job_id'] = $jobId;
    			// check if the value of email_each_transcript_on_complete for the job
    			$jobMapper = new Application_Model_JobMapper();
    			if (!$jobMapper->hasEmailEachTranscriptOnComplete($jobId)) {
    				// if all audio jobs are completed then send notification
    				$completedCount = $audioJobMapper->fetchCompletedCountByJobId($jobId);
    				$count = $audioJobMapper->fetchCountByJobId($jobId);
    				if ($count == $completedCount) {
    					// do nothing - proceed to send email
    					$options['emailType'] = 'audioJobReturnedToClientWholeJob';
    					$includeAttachment = false;
                        $view->jobTitle = $jobData['job_title'];
    				} else {
    					return;
    				}
    			}

    			$transcriptionFileMapper = new Application_Model_TranscriptionFileMapper();
    			$data = $transcriptionFileMapper->fetchLatestByAudioJobId($options['id']);


    			$view->data = $data;

    			$subject = 'Take Note: Completed transcript(s)';

    			if ($includeAttachment)
                {
                    $mail = $this->_attachTranscription($mail, $data);
    			}
    			break;

    		case 'jobCompleted':
    			$jobMapper = new Application_Model_JobMapper();
    			$data = $jobMapper->fetchById($options['id']);
    			$view->data = $data;
                $options['job_id'] = $options['id'];
    			$subject = 'Completed transcript';
    			break;

			case 'jobChangeRequest':
				$jobChangeRequestMapper = new Application_Model_JobChangeRequestMapper();
    			$data = $jobChangeRequestMapper->fetchById($options['id']);
    			$view->data = $data;
                $options['job_id'] = $data['job_id'];
    			$subject = 'Your change request has been received';
                $forceReceiver = $config->app->email->bookingEmail;
    			break;

    		default:
    			return false;
    			break;
    	}

        if (isset($options['job_id']))
        {
            $jobMapper       = new Application_Model_JobMapper();
            $jobData         = $jobMapper->fetchById($options['job_id']);
            //echo '<pre>'.print_r($jobData,true).'</pre>';exit;
            $view->addressee = $jobData['primary_user'];
            $view->job_title = $jobData['title'];

            // set up recipients
            if (!is_null($jobData['created_by_email']) && $jobData['created_by_email'] != '') {
                if (empty($forceReceiver))
                {
                    $mail->addTo($jobData['created_by_email']);
                }
                $view->addressee = $jobData['created_by'];
            }

            if (!is_null($jobData['primary_user_email']) && $jobData['primary_user_email'] != '') {
                if (empty($forceReceiver))
                {
                    $mail->addTo($jobData['primary_user_email']);
                }

                $view->addressee = $jobData['primary_user'];
            }

            if (!is_null($jobData['additional_transcript_recipients']) && $jobData['additional_transcript_recipients'] != '') {
                $recipients = explode(',', $jobData['additional_transcript_recipients']);
                foreach($recipients as $recipient) {
                    $mail->addTo(trim($recipient));
                }
            }
            if (!empty($forceReceiver))
            {
                $mail->addTo($forceReceiver);
            }
            $view->data = $data;
        }
        elseif (isset($options['user_id']))
        {
            $userMapper = new Application_Model_UserMapper;
            $user       = $userMapper->fetchById($options['user_id']);
            $view->addressee = $user['name'];
            if (!empty($forceReceiver))
            {
                $mail->addTo($forceReceiver);
            }
            else
            {
                $mail->addTo($user['email']);
            }
        }

        $view->appUrl           = $appUrl;
        $view->appName          = $config->app->name;
        $view->bookingEmail     = $config->app->email->bookingEmail;
        $view->transcriptsEmail = $config->app->email->transcriptsEmail;
        $view->bookingTelephone = $config->app->email->bookingTelephone;

        $userData  = Zend_Auth::getInstance()->getIdentity();
        $userModel = new Application_Model_UserMapper;
        $user      = $userModel->fetchById( $userData->id );
        $view->loggedinUser = $user;

    	$message = $view->render('email/' . $options['emailType'] . '.phtml');

    	// multiple recipients
    	$toDefault = $config->app->email->adminEmailAddress;

        // default system settings
        $defaultEmail = $config->app->email->defaultEmail;
        $appName      = $config->app->name;

    	$mail->setBodyHtml($message);

        if ( false === $fromSet )
        {
    	    $mail->setFrom($defaultEmail, $appName);
        }

        // CC into all emails
        $mail->addBcc($config->app->email->systemEmail);

        $mail->setSubject($subject);

    	$mail->send();
    }

    /**
     * Attach transcription file to email
     *
     * @param Zend_Mail $mail Mail object
     * @param array     $data Data array
     *
     * @return Zend_Mail
     */
    protected function _attachTranscription($mail, $data)
    {
        $fileContents = file_get_contents('../data/transcription/' . $data['id']);
        $mail->createAttachment(
            $fileContents,
            $data['mime_type'],
            Zend_Mime::DISPOSITION_ATTACHMENT,
            Zend_Mime::ENCODING_BASE64,
            $data['file_name']
        );

        return $mail;
    }

}