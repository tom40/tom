<?php

class AudioJobController extends App_Controller_Action
{
    /**
     * Shift mapper object
     * @var Application_Model_UsersShiftMapper
     */
    protected $_shiftMapper;

    /**
     * Default shift mapper object
     * @var Application_Model_UsersShiftMapper
     */
    protected $_defaultShiftMapper;

	public function init()
	{
        $this->flashMessenger = $this->_helper->flashMessenger;
		$this->_email = $this->_helper->getHelper('email');
        parent::init();
	}

	public function indexAction()
	{
	}

	public function deleteUploadAction()
	{
		$this->view->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$id = $this->_request->getParam('id');

		if (!empty($id)) {
			$mapper  = new Application_Model_AudioJobMapper();
			$data = array();
			$data['id'] = $id;

			// decrement the file_upload_count fields for any files with the same upload_key value - otherwise the email notification
			// for file uploads won't trigger as the file_upload_count will include the file we are about to delete
			$values = $mapper->fetchById($id);
			if ($values['upload_file_count'] > 0) {
				$uploadFileCount = $values['upload_file_count'];
				$uploadFileCount--;
			} else {
				$uploadFileCount = 0;
			}

			$uploadKey = $values['upload_key'];
			$mapper->changeFileUploadCountByUploadKey($uploadFileCount, $uploadKey);

			// now remove the file
			$mapper->deleteById($id);

			// check if should send email notification
			$uploadKeyCount = $mapper->getUploadKeyCountForFilesWithLengthIsNotNull($uploadKey);

			if ($uploadKeyCount == $uploadFileCount) {

				// update the records with $uploadKey
				$mapper->setEmailNotificationByUploadKey($uploadKey);

				// send email notification
				$options = array(
			    			'emailType' => 'audioJobReceived',
			    			'uploadKey' => $uploadKey,
                            'job_id'    => $values['job_id']
				);
				$this->_email->send($this->view, $options);
			}

			$output['status'] = 'ok';
		} else {
			$output['status'] = 'fail';
		}

		echo json_encode($output);
	}

	public function archiveAction()
	{
		$this->view->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$id = $this->_request->getParam('id');

		$audioJobCanArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'audio-job','archive');
		if ($audioJobCanArchive) {
			if (!empty($id)) {
				$mapper  = new Application_Model_AudioJobMapper();
				$data            = array();
				$data['id']      = $id;
				$data['deleted'] = date( 'Y-m-d H:i:s' );
				$mapper->save($data);

				$output['status'] = 'ok';
			} else {
				$output['status'] = 'fail';
			}
		} else {
			$output['status'] = 'fail';
		}
		echo json_encode($output);
	}

	public function cleanupOnUploadCloseAction()
	{
		$this->view->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		// action body
		$request = $this->getRequest();
		$jobId = $this->_request->getParam('job_id');

		$audioJobMapper = new Application_Model_AudioJobMapper();

		// remove any records which have no length_seconds for this job id and which have not had an email notification sent
		$uploadKey = $audioJobMapper->cleanupUploadsByJobId($jobId);

// 		Zend_Debug::dump($uploadKey);

		if (!is_null($uploadKey)) {
// 			Zend_Debug::dump($uploadKey);
			// check if should send email notification
// 			$uploadKeyCount = $mapper->getUploadKeyCountForFilesWithLengthIsNotNull($uploadKey);

// 			if ($uploadKeyCount == $uploadFileCount) {

				// update the records with $uploadKey
				$audioJobMapper->setEmailNotificationByUploadKey($uploadKey);

				// send email notification
				$options = array(
						    			'emailType' => 'audioJobReceived',
						    			'uploadKey' => $uploadKey,
                                        'job_id'    => $jobId
				);
				$this->_email->send($this->view, $options);
// 			}
		}

	}


    public function editAction()
    {
    	$output = array();
	    $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);

	    $audioJobCanEdit = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'audio-job','edit');
	    if ($audioJobCanEdit)
        {
            $id             = $this->_request->getParam('id');
            $idArray        = explode('-', $id);
            $isMulti        = (count($idArray) > 1);
            $useId          = $idArray[0];
            $audioJobMapper = new Application_Model_AudioJobMapper();
		    $data           = $audioJobMapper->fetchById($useId);
            $data['id']     = $id;

            $this->view->isMulti  = $isMulti;
            $this->view->idString = $id;

            $form     = new Application_Form_AudioJobEdit($data['client_id'], $id);


		    if ($this->getRequest()->isPost())
            {
                $formData = $this->getRequest()->getPost();

                $valid  = new Zend_Validate_GreaterThan(array('min' => 0));
                $valid->setMessage(
                      'Please select an option',
                          Zend_Validate_GreaterThan::NOT_GREATER
                );

                if ( isset( $formData['service_id'] ) )
                {
                    $form->getElement( 'service_id' )
                        ->setRequired( true )
                        ->addValidator( $valid );
                }
                else
                {
                    $form->getElement( 'transcription_type_id' )
                         ->setRequired( true )
                         ->addValidator( $valid );
                }

		    	if ($form->isValid($formData))
                {
                    if (isset($formData['length_hours']))
                    {
                        $formData['length_seconds'] = $audioJobMapper->converTimeToSeconds($formData['length_hours'], $formData['length_mins']);
                        unset($formData['length_hours']);
                        unset($formData['length_mins']);
                    }

                    if ( !isset( $formData['price_modifiers'] ) || !is_array( $formData['price_modifiers'] ) )
                    {
                        $formData['price_modifiers'] = array();
                    }

                    foreach ($idArray as $id)
                    {
                        $formData['id'] = $id;
		    		    $audioJobMapper->save($formData);
                    }

                    $message = 'You have edited ' . count($idArray) . ' audio jobs';
                    $this->flashMessenger->addMessage(array('notice' => $message));

		    		$output['status'] = 'ok';
		    		echo json_encode($output);
		    	}
                else
                {
                    $this->_viewEditForm( $form, $data, $audioJobMapper, $isMulti, $idArray, 'invalid' );
		    	}
		    }
            else
            {
                $this->_viewEditForm( $form, $data, $audioJobMapper, $isMulti, $idArray );
		    }
    	}
        else
        {
    		$output['status'] = 'fail';
    		echo json_encode($output);
    	}
    }

    /**
     * Display edit form
     *
     * @param Application_Form_AudioJobEdit    $form           Form object
     * @param array                            $data           Audio job data
     * @param Application_Model_AudioJobMapper $audioJobMapper Audio job model
     * @param bool                             $isMulti        will the form edit multiple audio jobs
     * @param array                            $idArray        Array of ids to edit
     * @param string                           $outputStatus   Ajax output status
     *
     * @return void
     */
    public function _viewEditForm( $form, $data, $audioJobMapper, $isMulti, $idArray, $outputStatus = 'ok' )
    {
        $taMapper = new Application_Model_TranscriptionPriceMapper();
        $form->populate($data);

        if (!$isMulti)
        {
            $form = $this->_populateTimeFields($form, $data['length_seconds']);
        }

        $this->view->audioJobs = $this->_getAudioJobsByJobId( $data['job_id'], $idArray );
        $this->view->leadId    = $data['lead_id'];

        $form->setDefault('speaker_numbers_id', $data['speaker_numbers_id']);
        $form->setDefault('audio_quality_id', $data['audio_quality_id']);
        $form->setDefault('turnaround_time_id', $data['turnaround_time_id']);

        if ( empty( $data['service_id'] ) )
        {
            $form->setDefault('transcription_type_id', $data['transcription_type_id']);

            $mapper = new Application_Model_SpeakerNumbersMapper();
            $items  = $mapper->fetchAllForDropdown();

            if (count($items) > 1)
            {
                array_unshift($items, array('key' => '0', 'value' => '-- select --'));
            }
            $form->getElement( 'speaker_numbers_id' )->addMultiOptions( $items );

            $items = $taMapper->fetchTurnaroundTimesForQuoteGenerator($data['transcription_type_id']);

            if ( count( $items ) > 1 )
            {
                array_unshift($items, array('key' => '0', 'value' => '-- select --'));
            }
            $form->getElement('turnaround_time_id')->addMultiOptions($items);

            $mapper = new Application_Model_TranscriptionTypeMapper();
            $items = $mapper->fetchAllForDropdown($this->_clientId);

            if (count($items) > 1)
            {
                array_unshift($items, array('key' => '0', 'value' => '-- select --'));
            }
            $form->getElement('transcription_type_id')->addMultiOptions($items);
        }
        else
        {
            $jobMapper = new Application_Model_JobMapper();
            $job       = $jobMapper->fetchRow( 'id = ' . $data['job_id'] );

            $this->view->audioJob = $audioJobMapper->fetchRow( 'id = ' . $idArray[0] );

            $clientMapper = new Application_Model_ClientMapper();
            $client       = $clientMapper->fetchRow( 'id = ' . $job->client_id );

            $form->setDefault( 'service_id', $data['service_id'] );

            $emptyValue = array( 0 => '-- Select --' );

            $services = App_Db_Manipulate::convertToDropDown( $client->getAllServices(), $emptyValue );
            $form->getElement( 'service_id' )->addMultiOptions( $services );

            $serviceMapper = new Application_Model_Service();
            $service       = $serviceMapper->fetchRow( 'id = ' . $data['service_id'] );

            $speakerNumbers  = $emptyValue + $service->getServiceSpeakerNumbersDropDown();
            $turnAroundTimes = $emptyValue + $service->getServiceTurnaroundTimesDropDown();

            $this->view->service = $service;

            $form->getElement( 'speaker_numbers_id' )->addMultiOptions( $speakerNumbers );
            $form->getElement( 'turnaround_time_id' )->addMultiOptions( $turnAroundTimes );
        }

        $this->view->form = $form;
        $this->view->id   = $data['id'];

        $this->view->isInvoice = $this->getRequest()->getParam( 'is-invoice', null );

        $outputHtml= $this->view->render('audio-job/edit.phtml');

        $output['html']   = $outputHtml;
        $output['status'] = $outputStatus;
        echo json_encode($output);
    }

    /**
     * Fetch client service details ajax for link form
     *
     * @return void
     */
    public function fetchServiceDetailsEditAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $serviceId = $this->getRequest()->getParam( 'service_id', null );
        $jobId     = $this->getRequest()->getParam( 'audio_job_id', null );

        $output = array(
            'turnaround_times' => '-',
            'speaker_numbers'  => '-'
        );

        if ( $serviceId > 0 )
        {
            $form       = new Application_Form_AudioJobEdit();
            $emptyValue = array( 0 => '-- Select --' );

            $serviceMapper = new Application_Model_Service();
            $service       = $serviceMapper->fetchRow( 'id = ' . $serviceId );

            $speakerNumbers  = $emptyValue + $service->getServiceSpeakerNumbersDropDown();
            $turnAroundTimes = $emptyValue + $service->getServiceTurnaroundTimesDropDown();


            $jobMapper = new Application_Model_AudioJobMapper();
            $job       = $jobMapper->fetchRow( $jobMapper->select()->where( 'id = ?', $jobId ) );

            $form->getElement( 'speaker_numbers_id' )->addMultiOptions( $speakerNumbers );
            $form->setDefault( 'speaker_numbers_id', $job->speaker_numbers_id );
            $form->getElement( 'turnaround_time_id' )->addMultiOptions( $turnAroundTimes );
            $form->setDefault( 'turnaround_time_id', $job->turnaround_time_id );

            $output['speaker_numbers']  = (string) $form->speaker_numbers_id;
            $output['turnaround_times'] = (string) $form->turnaround_time_id;
            $output['modifiers']        = $this->view->partial( 'job/_partials/_priceModifiers.phtml', 'default', array( 'service' => $service, 'audioJob' => $job ) );
        }

        echo json_encode( $output );
    }

    protected function _populateTimeFields($form, $lengthSeconds)
    {
        $hours   = 0;
        $minutes = 0;

        if (!empty($lengthSeconds))
        {
            $hours             = floor($lengthSeconds / (60 * 60));
            $divisorForMinutes = $lengthSeconds % (60 * 60);
            $minutes           = floor($divisorForMinutes / 60);
        }

        $form->getElement('length_hours')->setValue($hours);
        $form->getElement('length_mins')->setValue($minutes);
        return $form;
    }

    /**
     * Extra services
     *
     * @return void
     */
    public function extraServicesAction()
    {
	    $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost())
        {
            $data         = $this->getRequest()->getPost();
            $jobId        = $data['job_id'];
            $serviceId    = $data['service_id'];
            $audioService = new Application_Model_AudioJobServiceMapper;
            $insert = array(
                'audio_job_id' => $jobId,
                'service_id'   => $serviceId
            );
            $audioService->insert($insert);
        }
        else
        {
            $jobId = $this->_request->getParam('job_id');
        }

        $mapper   = new Application_Model_AudioJobMapper;
        $audioJob = $mapper->fetchById($jobId);

        $discountForm = new Application_Form_AudioJobDiscount();
        $discountForm->setDefault( 'audio_job_discount', $audioJob['audio_job_discount'] );

        $this->view->discountForm = $discountForm;
        $this->view->job          = $audioJob;
        $extraServices            = new Application_Model_AdditionalServicesMapper;
        $this->view->services     = $extraServices->fetchAllForDropdown($jobId);
        $this->view->current      = $extraServices->getServicesByAudioJob($jobId);
        $outputHtml               = $this->view->render('job/extra-services.phtml');
        $outputStatus             = 'ok';
        $output                   = array();
        $output['html']           = $outputHtml;
        $output['status']         = $outputStatus;

        echo json_encode($output);
    }

    /**
     * Apply discount to audio job
     *
     * @return void
     */
    public function applyDiscountAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $output = array();
        $data   = $this->getRequest()->getPost();
        $form   = new Application_Form_AudioJobDiscount();
        if ( !$form->isValid( $data ) )
        {
            $output['status'] = 'error';
        }
        else
        {
            $audioJobMapper = new Application_Model_AudioJobMapper();
            $update = array(
                'audio_job_discount' => $data['audio_job_discount']
            );
            $audioJobMapper->update( $update, "id='" . $data['audio_job_id'] . "'" );
            $this->flashMessenger->addMessage( array( 'notice' => 'You have edited audio job ID ' . $data['audio_job_id'] ) );
            $output['status'] = 'ok';
        }
        echo json_encode( $output );
    }

    /**
     * Show poor audio
     *
     * @return void
     */
    public function showPoorAudioAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $audioJobId = $this->getRequest()->getParam( 'audio_job_id' );
        $model      = new Application_Model_AudioJobMapper();
        $audioJob   = $model->fetchRow( 'id = ' . $audioJobId );

        $this->view->id         = $audioJob->id;
        $this->view->poor_audio = $audioJob->poor_audio;

        $outputHtml = $this->view->render('audio-job/poor-audio.phtml');

        echo json_encode( array( 'html' => $outputHtml ) );
    }

    /**
     * Mark an audio job as poor/not poor audio quality
     *
     * @return void
     */
    public function togglePoorAudioAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $data       = $this->getRequest()->getPost();
        $model      = new Application_Model_AudioJobMapper();
        $audioJob   = $model->fetchRow( 'id = ' . $data['audio_job_id'] );

        $output = array();
        if ( $audioJob->togglePoorAudio( $data['poor_audio'] ) )
        {
            $output['status'] = 'ok';
        }
        else
        {
            $output['status'] = 'invalid';
        }
        echo json_encode( $output );
    }

    /**
     * Delete extra service
     *
     * @return void
     */
    public function removeExtraServiceAction()
    {
        $output = array();
	    $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost())
        {
            $data         = $this->getRequest()->getPost();
            $jobId        = $data['job_id'];
            $serviceId    = $data['service_id'];
            $audioService = new Application_Model_AudioJobServiceMapper;
            $audioService->removeService($jobId, $serviceId);
            $output['status']     = 'ok';
        }
        else
        {
            $output['status'] = '';
        }
        echo json_encode($output);
    }

    /**
     * Get other audio jobs of the same job
     *
     * @param int $jobId Job ID
     *
     * @return Zend_Db_Table_Rowset
     */
    protected function _getAudioJobsByJobId($jobId, $audioJobId)
    {
        if ($this->_acl->isAdmin())
        {
            $jobModel  = new Application_Model_AudioJobMapper;
            if ( is_array( $audioJobId ) )
            {
                if ( false === $jobModel->audioJobsAreSameJob( $audioJobId ) )
                {
                    return array();
                }
            }
            $audioJobs = $jobModel->fetchByJobId($jobId, true, $audioJobId);
            return $audioJobs;
        }
    }

    public function showEditStatusesAction()
    {
        $staff      = $this->getRequest()->getParam( 'staff', false );
        $audioJobId = $this->getRequest()->getParam( 'audio_job_id' );

    	$output = array();
    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

    	$audioJobMapper   = new Application_Model_AudioJobMapper();
    	$audioJobStatuses = $audioJobMapper->fetchAllStatusesForDropdown( $staff );

    	$this->view->data       = $audioJobStatuses;
        $this->view->audioJobId = $audioJobId;
    	$this->_helper->viewRenderer->setNoRender(true);
    	$outputHtml     = $this->view->render( 'audio-job/show-edit-statuses.phtml' );
    	$output['html'] = $outputHtml;

    	$outputStatus = 'ok';
    	$output['status'] = $outputStatus;

    	echo json_encode($output);
    }

    public function showSoundFileDetailsAction()
    {
    	$output = array();
    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

		$audioJobMapper = new Application_Model_AudioJobMapper();
    	$audioJobId = $this->_request->getParam('audio_job_id');
    	$audioJob = $audioJobMapper->fetchById($audioJobId);

		$this->view->data = $audioJob;
		$this->_helper->viewRenderer->setNoRender(true);
    	$outputHtml = $this->view->render('audio-job/sound-file-details.phtml');
    	$output['html'] = $outputHtml;

		$audioJobDownloadMapper = new Application_Model_AudioJobDownloadMapper();
		$audioJob = $audioJobDownloadMapper->fetchByAudioJobId($audioJobId);

		$this->view->data = $audioJob;
		$this->_helper->viewRenderer->setNoRender(true);
		$outputHtml = $this->view->render('audio-job/download-history.phtml');
		$output['html_history'] = $outputHtml;

		$outputStatus = 'ok';
		$output['status'] = $outputStatus;

		echo json_encode($output);
    }

    public function showTranscriptionFileListAction()
    {
    	$output = array();
    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

    	$audioJobId = $this->_request->getParam('audio_job_id');

    	$transcriptionFileMapper = new Application_Model_TranscriptionFileMapper();

        $this->view->showComments = true;
        // Clients only see the latest file
        $showLatestRecordOnly = false;
        if (Application_Model_UserMapper::CLIENT_ACL_GROUP_ID === $this->_identity->acl_group_id)
        {
            $this->view->showComments = false;
            $showLatestRecordOnly     = true;

            $jobMapper = new Application_Model_JobMapper();
            $audioJobMapper = new Application_Model_AudioJobMapper();
            $audioJob       = $audioJobMapper->fetchById($audioJobId);
            $job            = $jobMapper->fetchById($audioJob['job_id']);

            $primaryUserId  = $job['primary_user_id'];
            $currentUserId  = Zend_Auth::getInstance()->getIdentity()->id;

            // For the primary user id we want them to be able to grant access to other colleagues
            if ($currentUserId == $primaryUserId)
            {
                // Get client colleagues
                $clientUserMapper                 = new Application_Model_ClientsUserMapper();
                $clientUser                       = $clientUserMapper->fetchByUserId($currentUserId);
                $colleagues                       = $clientUserMapper->fetchClientUserColleagues($currentUserId, $clientUser['client_id']);
                $this->view->clientUserColleagues = $colleagues;
                $this->view->audioJobId           = $audioJobId;
            }
        }

    	$transcriptionFiles = $transcriptionFileMapper->fetchByAudioJobId($audioJobId, $showLatestRecordOnly);
    	$this->view->data   = $transcriptionFiles;

    	$transcriptionCanArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'transcription-file','archive');
    	$this->view->canArchive = $transcriptionCanArchive;

    	$this->_helper->viewRenderer->setNoRender(true);
    	$outputHtml = $this->view->render('audio-job/transcription-file-list.phtml');
    	$output['html'] = $outputHtml;

    	$outputStatus = 'ok';
    	$output['status'] = $outputStatus;

    	echo json_encode($output);
    }

    /**
     * Grant shared access to colleagues
     *
     * @return void
     */
    public function grantUserAccessAction()
    {
        $output = array();
        $outputStatus = 'ok';
        $message = '';

        if ($this->getRequest()->isPost())
        {
            $formData   = $this->getRequest()->getPost();
            $audioJobId = $formData['audio_job_id'];

            $aclMapper        = new Application_Model_AclMapper();
            $jobMapper        = new Application_Model_JobMapper();
            $audioJobMapper   = new Application_Model_AudioJobMapper();
            $userMapper       = new Application_Model_UserMapper();
            $clientUserMapper = new Application_Model_ClientsUserMapper();

            $audioJob              = $audioJobMapper->fetchById($audioJobId);
            $jobId                 = $audioJob['job_id'];
            $job                   = $jobMapper->fetchById($jobId);
            $jobAclResourceId      = $job['acl_resource_id'];
            $audioJobAclResourceId = $audioJob['acl_resource_id'];

            $usersAllowedAccess = array();
            if (isset($formData['access']))
            {
                $usersAllowedAccess = $formData['access'];

                $colleagueProjectPrivileges  = array(1, 10, 11, 22, 38, 41, 42, 9);
                $colleagueAudioJobPrivileges = array(5, 26, 18);
                $audioAccessPrivilegeId      = 26; // show-transcription-file-list

                if (!empty($usersAllowedAccess))
                {
                    foreach ($usersAllowedAccess as $userId)
                    {
                        $user          = $userMapper->fetchById($userId);
                        $userAclRoleId = $user['acl_role_id'];
                        // Check if this user already has access to this audio file
                        if (!$aclMapper->hasResourceAccess($userAclRoleId, $audioJobAclResourceId, $audioAccessPrivilegeId))
                        {
                            // Allow access to job (project)
                            foreach ($colleagueProjectPrivileges as $privilegeId)
                            {
                                if (!$aclMapper->aclExists($userAclRoleId,$jobAclResourceId,$privilegeId,'allow' ))
                                {
                                    $data = array('role_id' => $userAclRoleId, 'resource_id' => $jobAclResourceId, 'privilege_id' => $privilegeId, 'mode' => 'allow');
                                    $aclMapper->insert($data);
                                }
                            }

                            // Allow user to access the audio jobs
                            foreach ($colleagueAudioJobPrivileges as $privilegeId)
                            {
                                if (!$aclMapper->aclExists($userAclRoleId,$audioJobAclResourceId,$privilegeId,'allow' ))
                                {
                                    $data = array('role_id' => $userAclRoleId, 'resource_id' => $audioJobAclResourceId, 'privilege_id' => $privilegeId, 'mode' => 'allow');
                                    $aclMapper->insert($data);
                                }
                            }
                        }
                    }
                }

                $message = 'The selected colleagues have been granted access to this audio job';
            }
            else
            {
                $message = 'Access has been removed from all users';
            }

            $currentUserId = Zend_Auth::getInstance()->getIdentity()->id;
            $clientUser    = $clientUserMapper->fetchFullDataByUserId($currentUserId);

            // Remove privileges from all colleagues not granted access
            $colleagues = $clientUserMapper->fetchClientUserColleagues($currentUserId, $clientUser['client_id']);
            if (!empty($colleagues))
            {
                foreach ($colleagues as $colleague)
                {
                    $colleagueAclRoleId = $colleague['acl_role_id'];
                    $colleagueUserId    = $colleague['user_id'];
                    if (!in_array($colleagueUserId, $usersAllowedAccess))
                    {
                        // Remove access from project and audio file
                        $aclMapper->removeTranscriptAccess($colleagueAclRoleId, $jobAclResourceId, $audioJobAclResourceId);
                    }
                }
            }
        }

        $output['status'] = $outputStatus;
        $output['message'] = $message;
        echo json_encode($output);
        exit();
    }

    protected function _initialiseShiftObjects($userType = Application_Model_UsersShiftMapper::TYPIST_SHIFT)
    {
        $this->_defaultShiftMapper = Application_Model_DefaultShiftMapperFactory::getObject($userType);
        $this->_shiftMapper        = Application_Model_ShiftMapperFactory::getObject($userType);
    }

    /**
     * Un-assign typist
     *
     * @return void
     */
    public function unassignTypistAction()
    {
        $this->view->hasTopMenu = false;
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $audioJobIdString = $this->_request->getParam('audio_job_id');
        $audioJobIdArray  = explode('-', $audioJobIdString);

        $audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
        $audioJobMapper       = new Application_Model_AudioJobMapper();

        foreach ($audioJobIdArray as $audioJobId)
        {
            $newlyAssignedUsers   = array();
            $currentAssignedUsers = $audioJobTypistMapper->fetchTypistList($audioJobId);
            $this->_removeUnassignedUsers($audioJobTypistMapper, $audioJobId, $currentAssignedUsers, $newlyAssignedUsers, 'audioJobTypistCancelled');
            $audioJobTypistMapper->unsplitAudioJob($audioJobId);

            $audioJobMapperData = array(
                'id'        => $audioJobId,
                'status_id' => 3
            );

            $audioJobMapper->save($audioJobMapperData);
        }

        $subStandard = $this->getRequest()->getParam( 'substandard_payrate', null );
        if ( !empty( $subStandard ) )
        {
            $data = array(
                'id'                  => $subStandard,
                'current'             => 0,
                'substandard_payrate' => 1
            );
            $audioJobTypistMapper->save( $data );
        }

        echo json_encode(array('status' => 'ok'));
    }

    /**
     * Un-assign typist
     *
     * @return void
     */
    public function unassignProofreaderAction()
    {
        $this->view->hasTopMenu = false;
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $audioJobIdString = $this->_request->getParam('audio_job_id');
        $audioJobIdArray  = explode('-', $audioJobIdString);

        $audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
        $audioJobMapper            = new Application_Model_AudioJobMapper();

        foreach ($audioJobIdArray as $audioJobId)
        {
            $newlyAssignedUsers   = array();
            $currentAssignedUsers = $audioJobProofreaderMapper->fetchProofreaderList($audioJobId);
            $this->_removeUnassignedUsers($audioJobProofreaderMapper, $audioJobId, $currentAssignedUsers, $newlyAssignedUsers, 'audioJobProofreaderCancelled');
            $audioJobProofreaderMapper->unsplitAudioJob($audioJobId);

            $audioJobMapperData              = array(
                'id'        => $audioJobId,
                'status_id' => 3
            );

            $audioJobMapper->save($audioJobMapperData);
        }
        echo json_encode(array('status' => 'ok'));
    }

    public function assignTypistAction()
    {
        $output                 = array();
        $form                   = new Application_Form_AssignTypist();
        $this->view->hasTopMenu = false;
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $audioJobIdString = $this->_request->getParam('audio_job_id');
        $audioJobIdArray  = explode('-', $audioJobIdString);
        $audioJobId       = $audioJobIdArray[0];
        $audioJobMapper   = new Application_Model_AudioJobMapper();
        $mainAudioJob     = $audioJobMapper->fetchByIdBasic($audioJobId);

        $this->_initialiseShiftObjects(Application_Model_UsersShiftMapper::TYPIST_SHIFT);
        if ( $this->getRequest()->isPost() )
        {
            $typistId = $this->_request->getParam('user_id');
            $shiftId  = $this->_request->getParam('shift_time');
            $dueDate  = $this->_request->getParam('due_date');
            $comment  = $this->_request->getParam('comment');
            $comment == '' ? $comment = null : null;

            // Get current typist
            $audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
            $subStandard          = $this->getRequest()->getParam( 'substandard_payrate', null );

            foreach ($audioJobIdArray as $audioJobId)
            {
                $data               = array();
                $currentTypist      = $audioJobTypistMapper->fetchByAudioJobId($audioJobId);
                $replacementPayrate = false;

                if (!empty($currentTypist))
                {
                    foreach ($currentTypist as $key => $row)
                    {
                        if ($row['user_id'] !== $typistId)
                        {
                            $removeTypistData = array(
                                'id'                  => $row['id'],
                                'current'             => 0
                            );

                            if ( count( $audioJobIdArray ) === 1 )
                            {
                                if ( is_array( $subStandard ) && count( $subStandard ) > 0 )
                                {
                                    foreach ( $subStandard as $subTypist )
                                    {
                                        if ( $subTypist == $row['id'] )
                                        {
                                            $removeTypistData['substandard_payrate'] = '1';
                                        }
                                    }
                                }
                            }

                            $audioJobTypistMapper->save( $removeTypistData );

                            $this->_insertAcl($audioJobId, $row['acl_role_id'], 18, 'deny');
                            $options = array(
                                'emailType' => 'audioJobTypistCancelled',
                                'id'        => $row['id']
                            );
                            $this->_email->send($this->view, $options);

                            if( '1' == $row['replacement_payrate'] )
                            {
                                $replacementPayrate = true;
                            }
                        }
                        unset($currentTypist[$key]);
                    }
                }

                if (!empty($currentTypist))
                {
                    $currentTypist = array_shift($currentTypist);
                    $data['id']    = $currentTypist['id'];
                }

                $data['audio_job_id'] = $audioJobId;
                $data['user_id']      = $typistId;
                $data['shift_id']     = $shiftId;
                $data['due_date']     = $dueDate;
                $data['comment']      = $comment;

                if ( $replacementPayrate || null !== $subStandard )
                {
                    $data['replacement_payrate'] = '1';
                }

                $audioJobsTypistsId = $audioJobTypistMapper->save($data);

                $audioJobMapperData              = array();
                $audioJobMapperData['id']        = $audioJobId;
                $audioJobMapperData['status_id'] = 5;
                $audioJobMapper->save($audioJobMapperData);

                if (!isset($data['id']))
                {
                    // add typist to acl resources for this object
                    $typist = $audioJobTypistMapper->fetchById($audioJobsTypistsId);
                    $this->_insertAcl($audioJobId, $typist['acl_role_id'], 18, 'allow');

                    // email notification
                    $options = array(
                        'emailType' => 'audioJobAssignedToTypist',
                        'id'        => $audioJobsTypistsId
                    );
                    $this->_email->send($this->view, $options);
                }
                else
                {
                    if ($currentTypist['due_date'] !== $dueDate)
                    {
                        // Send due date changed email
                        $options = array(
                            'emailType' => 'audioJobTypistDeadlineChanges',
                            'id'        => $currentTypist['id']
                        );
                        $this->_email->send($this->view, $options);
                    }
                }
            }
            $outputStatus = 'ok';
        }
        else
        {
            $id             = $this->_request->getParam('id');
            $audioJobTypist = new Application_Model_AudioJobTypistMapper();

    		// Find current typist
    		if ( !empty( $id ) )
            {
    			$typist         = $audioJobTypist->fetchById($id);
    			$form->setDefault('id', $id);
    			$form->setDefault('due_date', $typist['due_date']);
    			$this->view->user_id       = $typist['user_id'];

                if ( count( $audioJobIdArray ) === 1 )
                {
                    $this->view->currentTypist = $typist;
                }

                $form->setDefault('comment', $typist['comment']);

                $dueDate            = $typist['due_date'];
                $form               = $this->_setDuedateForView($form, $dueDate);
                $form               = $this->_populateSelectedShiftDropdown($form, $typist['shift_id']);
                $defaultShiftTimeId = $typist['shift_id'];
    		}
            else
            {
                $defaultShiftTimeId = $this->_defaultShiftMapper->getCurrentOrNextShiftDay();
                $shift              = $this->_defaultShiftMapper->fetchCurrentShift($defaultShiftTimeId);
                $shiftTimes         = $this->_defaultShiftMapper->getShiftTimesForDropdown($shift['start_day_number']);
                $form->getElement('shift_time')->addMultiOptions($shiftTimes);
                $form->setDefault('comment', $mainAudioJob['client_comments']);
                $form = $this->_populateSelectedShiftDropdown($form, $defaultShiftTimeId);
            }

            $this->view->subStandard = $audioJobTypist->fetchSubStandardRows( $audioJobId );
            $audioJobs               = $audioJobMapper->fetchWithClient($audioJobIdArray);
            $this->view->audioJobs   = $audioJobs;

            $trainingCodes = array();
            foreach ( $audioJobs as $audioJob )
            {
                if ( empty( $audioJob['training_code'] ) )
                {
                    break;
                }
                else
                {
                    $trainingCodes[] = $audioJob['training_code'];
                }
            }

            if ( count( $trainingCodes ) > 0 )
            {
                $this->view->hasTrainingFilter = true;
            }
            else
            {
                $this->view->hasTrainingFilter = false;
            }

            // Set due days
            $currentTimeStamp    = strtotime('today');
            $nextMondayTimeStamp = strtotime('monday');
            $nextSundayTimeStamp = strtotime('sunday');
            $noDaysToMonday = round(($nextMondayTimeStamp - $currentTimeStamp) / 86400);
            $noDaysToSunday = round(($nextSundayTimeStamp - $currentTimeStamp) / 86400);

            $this->view->dueDays = array('0' => 'Today', '1' => 'Tomorrow', $noDaysToSunday => 'Sunday', $noDaysToMonday => 'Monday');
            $dueTimes            = array('06:00' => '6am', '07:00' => '7am', '08:00' => '8am', '09:00' => '9am', '12:00' => 'Midday', '16:00' => '4pm', '17:00' => '5pm');
            $form->getElement('due_time')->addMultiOptions($dueTimes);

            // Pre-populate fields
            $shiftDays = $this->_defaultShiftMapper->getShiftDaysForDropdown();
            $form->getElement('shift_day')->addMultiOptions($shiftDays);

            $this->_fetchShiftUserList( $defaultShiftTimeId, null, $trainingCodes );
            $this->_setFullFilename($audioJobId);

            $this->view->shiftId = $defaultShiftTimeId;
            $this->view->form    = $form;
            $outputHtml          = $this->view->render('audio-job/assign-typist.phtml');
            $outputStatus        = 'ok';

            isset($outputHtml2) 		? $output['html2'] 			= $outputHtml2 		: null;
            isset($outputHtml3) 		? $output['html3'] 			= $outputHtml3 		: null;
            isset($outputHtml4) 		? $output['html4'] 			= $outputHtml4 		: null;
            isset($outputHtml5) 		? $output['html5'] 			= $outputHtml5 		: null;
            isset($outputCssBgcolor)	? $output['css_bgcolor'] 	= $outputCssBgcolor : null;

            $output['html']   = $outputHtml;
        }

        $output['status'] = $outputStatus;
        echo json_encode($output);
    }

    /**
     * Remove liability action
     *
     * @return void
     */
    public function removeLiabilityAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $return        = array();
        $audioJobRowId = $this->getRequest()->getParam( 'audioJobTypistId' );
        $mapper        = new Application_Model_AudioJobTypistMapper();

        if ( $mapper->removeSubStandard( $audioJobRowId ) )
        {
            $return = array( 'status' => 'ok' );
        }
        echo json_encode( $return );
    }

    public function assignProofreaderAction()
    {
        $output                 = array();
        $form                   = new Application_Form_AssignProofreader();
        $this->view->hasTopMenu = false;
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $audioJobIdString = $this->_request->getParam('audio_job_id');
        $audioJobIdArray  = explode('-', $audioJobIdString);
        $audioJobId       = $audioJobIdArray[0];
        $audioJobMapper   = new Application_Model_AudioJobMapper();
        $mainAudioJob     = $audioJobMapper->fetchByIdBasic($audioJobId);

        $this->_initialiseShiftObjects(Application_Model_UsersShiftMapper::PROOFREADER_SHIFT);
        if ($this->getRequest()->isPost())
        {
            $typistId = $this->_request->getParam('user_id');
            $shiftId  = $this->_request->getParam('shift_time');
            $dueDate  = $this->_request->getParam('due_date');
            $comment  = $this->_request->getParam('comment');
            $comment == '' ? $comment = null : null;

            // Get current typist
            $audioJobTypistMapper = new Application_Model_AudioJobProofreaderMapper();

            foreach ($audioJobIdArray as $audioJobId)
            {
                $data          = array();
                $currentTypist = $audioJobTypistMapper->fetchByAudioJobId($audioJobId);

                if (!empty($currentTypist))
                {
                    foreach ($currentTypist as $key => $row)
                    {
                        if ($row['user_id'] !== $typistId)
                        {
                            $audioJobTypistMapper->save(
                                array('id' => $row['id'], 'current' => 0)
                            );

                            $this->_insertAcl($audioJobId, $row['acl_role_id'], 18, 'deny');
                            $options = array(
                                'emailType' => 'audioJobProofreaderCancelled',
                                'id'        => $row['id']
                            );
                            $this->_email->send($this->view, $options);
                        }
                        unset($currentTypist[$key]);
                    }
                }

                if (!empty($currentTypist))
                {
                    $currentTypist = array_shift($currentTypist);
                    $data['id']    = $currentTypist['id'];
                }

                $data['audio_job_id'] = $audioJobId;
                $data['user_id']      = $typistId;
                $data['shift_id']     = $shiftId;
                $data['due_date']     = $dueDate;
                $data['comment']      = $comment;

                $audioJobsTypistsId = $audioJobTypistMapper->save($data);

                $audioJobMapperData              = array();
                $audioJobMapperData['id']        = $audioJobId;
                $audioJobMapperData['status_id'] = 5;
                $audioJobMapper->save($audioJobMapperData);

                if (!isset($data['id']))
                {
                    // add typist to acl resources for this object
                    $typist = $audioJobTypistMapper->fetchById($audioJobsTypistsId);
                    $this->_insertAcl($audioJobId, $typist['acl_role_id'], 18, 'allow');

                    // email notification
                    $options = array(
                        'emailType' => 'audioJobAssignedToProofreader',
                        'id'        => $audioJobsTypistsId
                    );
                    $this->_email->send($this->view, $options);
                }
                else
                {
                    if ($currentTypist['due_date'] !== $dueDate)
                    {
                        // Send due date changed email
                        $options = array(
                            'emailType' => 'audioJobProofreaderDeadlineChanges',
                            'id'        => $currentTypist['id']
                        );
                        $this->_email->send($this->view, $options);
                    }
                }
            }
            $outputStatus = 'ok';
        }
        else
        {
            $id = $this->_request->getParam('id');

            // Find current typist
            if (!empty($id)) {
                $audioJobTypist = new Application_Model_AudioJobProofreaderMapper();
                $typist         = $audioJobTypist->fetchById($id);
                $form->setDefault('id', $id);
                $form->setDefault('due_date', $typist['due_date']);
                $this->view->user_id = $typist['user_id'];

                $form->setDefault('comment', $typist['comment']);

                $dueDate            = $typist['due_date'];
                $form               = $this->_setDuedateForView($form, $dueDate);
                $form               = $this->_populateSelectedShiftDropdown($form, $typist['shift_id']);
                $defaultShiftTimeId = $typist['shift_id'];
            }
            else
            {
                $defaultShiftTimeId = $this->_defaultShiftMapper->getCurrentOrNextShiftDay();
                $shift              = $this->_defaultShiftMapper->fetchCurrentShift($defaultShiftTimeId);
                $shiftTimes         = $this->_defaultShiftMapper->getShiftTimesForDropdown($shift['start_day_number']);
                $form->getElement('shift_time')->addMultiOptions($shiftTimes);
                $form->setDefault('comment', $mainAudioJob['client_comments']);
                $form = $this->_populateSelectedShiftDropdown($form, $defaultShiftTimeId);
            }

            $this->view->audioJobs = $audioJobMapper->fetchWithClient($audioJobIdArray);

            // Set due days
            $currentTimeStamp    = strtotime('today');
            $nextMondayTimeStamp = strtotime('monday');
            $nextSundayTimeStamp = strtotime('sunday');
            $noDaysToMonday = round(($nextMondayTimeStamp - $currentTimeStamp) / 86400);
            $noDaysToSunday = round(($nextSundayTimeStamp - $currentTimeStamp) / 86400);

            $this->view->dueDays = array('0' => 'Today', '1' => 'Tomorrow', $noDaysToSunday => 'Sunday', $noDaysToMonday => 'Monday');
            $dueTimes            = array('06:00' => '6am', '07:00' => '7am', '08:00' => '8am', '09:00' => '9am', '12:00' => 'Midday', '16:00' => '4pm', '17:00' => '5pm');
            $form->getElement('due_time')->addMultiOptions($dueTimes);

            // Pre-populate fields
            $shiftDays = $this->_defaultShiftMapper->getShiftDaysForDropdown();
            $form->getElement('shift_day')->addMultiOptions($shiftDays);

            $this->_fetchShiftUserList($defaultShiftTimeId);
            $this->_setFullFilename($audioJobId);

            $this->view->shiftId = $defaultShiftTimeId;
            $this->view->form    = $form;
            $outputHtml          = $this->view->render('audio-job/assign-proofreader.phtml');
            $outputStatus        = 'ok';

            isset($outputHtml2) 		? $output['html2'] 			= $outputHtml2 		: null;
            isset($outputHtml3) 		? $output['html3'] 			= $outputHtml3 		: null;
            isset($outputHtml4) 		? $output['html4'] 			= $outputHtml4 		: null;
            isset($outputHtml5) 		? $output['html5'] 			= $outputHtml5 		: null;
            isset($outputCssBgcolor)	? $output['css_bgcolor'] 	= $outputCssBgcolor : null;

            $output['html']   = $outputHtml;
        }

        $output['status'] = $outputStatus;
        echo json_encode($output);
    }

    protected function _setFullFilename($audioJobId)
    {
        // Generate full file name
        $mapper = new Application_Model_AudioJobMapper();
        $fullFileName = $mapper->getFullFileName($audioJobId);
        $this->view->fullFileName = $fullFileName;
    }

    protected function _setDuedateForView($form, $dueDate)
    {
        $todayDayName = date('l', strtotime('now'));
        $tomorrowDayName = date('l', strtotime('tomorrow'));

        $dueDayName = date('l', strtotime($dueDate));

        if ($todayDayName == $dueDayName)
        {
            $dueDayName = 'Today';
        }
        else if ($tomorrowDayName == $dueDayName)
        {
            $dueDayName = 'Tomorrow';
        }

        $dueHour = date('H:i', strtotime($dueDate));
        $this->view->selectedDueDay = $dueDayName;
        $form->getElement('due_time')->setValue($dueHour);
        return $form;
    }

    public function ajaxShiftTypistsAction()
    {
        $this->_initialiseShiftObjects(Application_Model_UsersShiftMapper::TYPIST_SHIFT);

        $id              = $this->_request->getParam('id');
        $audioJobIdArray = explode('-', $this->_request->getParam('audioJobIds', array()));

        $audioJobMapper        = new Application_Model_AudioJobMapper();
        $audioJobs             = $audioJobMapper->fetchWithClient($audioJobIdArray);
        $this->view->audioJobs = $audioJobs;

        $trainingCodes = array();
        foreach ( $audioJobs as $audioJob )
        {

            if ( empty( $audioJob['training_code'] ) )
            {
                break;
            }
            else
            {
                $trainingCodes[] = $audioJob['training_code'];
            }
        }

        $toggleDirection = $this->_request->getParam( 'toggle_direction', null );
        if ( 'off' === $toggleDirection )
        {
            $trainingCodes = array();
        }

        $audioJobTypist      = new Application_Model_AudioJobTypistMapper();
        $typist              = $audioJobTypist->fetchById($id);
        $this->view->user_id = $typist['user_id'];

        $this->_getShiftUsers( '_audioJobTypistList.phtml', $trainingCodes );
    }

    public function ajaxShiftProofreadersAction()
    {
        $this->_initialiseShiftObjects(Application_Model_UsersShiftMapper::PROOFREADER_SHIFT);

        $id                  = $this->_request->getParam('id');
        $audioJobProofreader = new Application_Model_AudioJobProofreaderMapper();
        $proofreader         = $audioJobProofreader->fetchById($id);
        $this->view->user_id = $proofreader['user_id'];

        $this->_getShiftUsers('_audioJobProofreaderList.phtml');
    }

    protected function _getShiftUsers( $view = '_audioJobTypistList.phtml', $trainingCodes = array() )
    {
        if ($this->getRequest()->isPost()) {
    		$formData = $this->getRequest()->getPost();

            $shiftId           = $formData['shift_time'];
            $selectedDayNumber = $formData['shift_day'];

            if (empty($shiftId))
            {
                $shiftId = $this->_defaultShiftMapper->getCurrentOrNextShiftDay();
            }

            $name = null;
            if (isset($formData['search']))
            {
                $name = $formData['search'];
            }

            $this->_fetchShiftUserList( $shiftId, $name, $trainingCodes );
        }

        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->view->shiftId = $shiftId;

        $outputHtml = $this->view->render('_partials/' . $view);
        $outputStatus     = 'ok';
        $output['status'] = $outputStatus;
        $output['html']   = $outputHtml;
        echo json_encode($output);
        exit();
    }

    public function ajaxTypistShiftDayTimesAction()
    {
        $this->_initialiseShiftObjects(Application_Model_UsersShiftMapper::TYPIST_SHIFT);
        $this->_getShiftDayTimes();
    }

    public function ajaxProofreaderShiftDayTimesAction()
    {
        $this->_initialiseShiftObjects(Application_Model_UsersShiftMapper::PROOFREADER_SHIFT);
        $this->_getShiftDayTimes();
    }

    protected function _getShiftDayTimes()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $selectedDayNumber = $this->_request->getParam('selected_day_number');
        $shiftTimes              = $this->_defaultShiftMapper->getShiftTimesForDropdown($selectedDayNumber);
        echo json_encode($shiftTimes);
        exit();
    }

    public function removeMultipleTypistAction()
    {
        $typistAudioJobId = $this->_request->getParam('typist_audio_job_id');

        if (!empty($typistAudioJobId))
        {
            $audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
            $typist               = $audioJobTypistMapper->fetchById($typistAudioJobId);

            $this->_insertAcl($typist['audio_job_id'], $typist['acl_role_id'], 18, 'deny');
            $this->_sendAssignedUpdateEmailNotification('audioJobTypistCancelled', $typistAudioJobId);

            $newData            = array();
            $newData['id']      = $typistAudioJobId;
            $newData['user_id'] = 0;
            $newData['current'] = 1;
            $audioJobTypistMapper->save($newData);
        }

        $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
        $output           = array();
        $output['status'] = 'ok';
        echo json_encode($output);
    }

    public function removeMultipleProofreaderAction()
    {
        $proofreaderAudioJobId     = $this->_request->getParam('proofreader_audio_job_id');
        if (!empty($proofreaderAudioJobId))
        {
            $audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
            $proofreader               = $audioJobProofreaderMapper->fetchById($proofreaderAudioJobId);

            $this->_insertAcl($proofreader['audio_job_id'], $proofreader['acl_role_id'], 18, 'deny');
            $this->_sendAssignedUpdateEmailNotification('audioJobProofreaderCancelled', $proofreaderAudioJobId);

            $newData            = array();
            $newData['id']      = $proofreaderAudioJobId;
            $newData['user_id'] = 0;
            $newData['current'] = 1;
            $audioJobProofreaderMapper->save($newData);
        }

        $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
        $output           = array();
        $output['status'] = 'ok';
        echo json_encode($output);
    }

    public function assignMultipleTypistAction()
    {
    	$output = array();

    	$form    = new Application_Form_AssignMultipleTypist();
    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

    	$data = array();
    	if ($this->getRequest()->isPost())
        {
            $audioJobId = $this->_request->getParam('audio_job_id');

            $params = $this->_request->getParams();
            foreach($this->_request->getParams() as $key => $param)
            {

                if (substr($key, 0, 7) == 'typist-')
                {
                    $keyValues = explode('-', $key);

                    if (!array_key_exists($keyValues[2], $data))
                    {
                        $data[$keyValues[2]] = array();
                        $data[$keyValues[2]]['audio_job_id'] = $keyValues[1];
                        $data[$keyValues[2]][$keyValues[3]] = $param;
                    }
                    else
                    {
                        $data[$keyValues[2]][$keyValues[3]] = $param;
                    }
                }
            }

            // Assign typist process
            $audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
            $audioJobStatusId     = 5; // Assigned to typist;

            if (!empty($data))
            {
                $assignedResult = $audioJobTypistMapper->assignMultipleTypists( $audioJobId, $data, $params['substandard_payrate'] );
                if ( $assignedResult['result'] === Application_Model_AudioJobTypistMapper::UPDATE_PART )
                {
                    $audioJobStatusId = 27;
                }

                if ( isset( $assignedResult['removed_user'] ) )
                {
                    foreach ( $assignedResult['removed_user'] as $userId )
                    {
                        $this->_sendAssignedUpdateEmailNotification( 'audioJobTypistCancelled', $userId );
                    }
                }

                if ( isset( $assignedResult['assigned_user'] ) )
                {
                    foreach ( $assignedResult['assigned_user'] as $userId )
                    {
                        $this->_sendAssignedUpdateEmailNotification( 'audioJobAssignedToTypist', $userId );
                    }
                }

            }

            // set audio job status to assigned to typist
            $audioJobMapper                  = new Application_Model_AudioJobMapper();
            $audioJobMapperData              = array();
            $audioJobMapperData['id']        = $audioJobId;
            $audioJobMapperData['status_id'] = $audioJobStatusId;
            $audioJobMapper->save($audioJobMapperData);

            $outputHtmlTmp = $this->_makeTypistCellHtml($audioJobId, null, null, null, $audioJobStatusId);

            // typist cell html
            $outputHtml[$audioJobId] = $outputHtmlTmp[0];

            // set status html
            $outputHtml2[$audioJobId] = $outputHtmlTmp[1];

            // set typist due date
            $outputHtml3[$audioJobId] = $outputHtmlTmp[2];

            // set typist due date sort
            $outputHtml4[$audioJobId] = $outputHtmlTmp[3];

            // set transcript
            $outputHtml5[$audioJobId] = $outputHtmlTmp[4];

            // set row bg colour
            $outputCssBgcolor[$audioJobId] = $outputHtmlTmp[5];

            $outputStatus = 'ok';
    	}
        else
        {
    		$audioJobId = $this->_request->getParam('audio_job_id');
    		$this->view->audioJobId = $audioJobId;

    		$mode = $this->_request->getParam('mode');


    		if ($mode == 'edit' || $mode == 'addRow')
            {
    			$audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
    			$data = $audioJobTypistMapper->fetchByAudioJobId($audioJobId, false, 'minutes_start ASC' );

    			// if adding a new row then get the start and end times for the all the rows (including the existing ones and new one being added)
    			if ($mode =='addRow')
                {
    				$audioJobMapper = new Application_Model_AudioJobMapper();
    				$audioJob = $audioJobMapper->fetchById($audioJobId);
    				$lengthSeconds = $audioJob['length_seconds'];

    				$typistCount = $this->_request->getParam('row_count');
    				$rowData = $this->_makeMultipleTypistRows($lengthSeconds, $typistCount+1);
    			}

    			// add a unique row id
    			$x = 1;
    			foreach ($data as &$item) {
    				$item['audioJobIdTypistNumber'] = $x;
    				if ($mode == 'addRow') {
    					$item['minutes_start'] = $rowData[$x]['minutes_start'];
    					$item['minutes_end'] = $rowData[$x]['minutes_end'];
    				}
    				$x++;
    			}

    			// add new element to data array for new row
    			if ($mode == 'addRow') {
    				for ($y = $x; $y <= $typistCount; $y++) {
		    			$data[] = array(
		    				'audioJobIdTypistNumber' 	=> $y,
		    				'due_date'	 				=> null,
		    				'minutes_start'	 			=> $rowData[$y]['minutes_start'],
		    				'minutes_end'	 			=> $rowData[$y]['minutes_end'],
		    			);
	    			}
    			}

    			$this->view->data = $data;
    		}

     		// reset mode to edit so that screen redraws correctly
    		$this->view->mode = $mode;


    		$this->view->form = $form;


    		$this->_helper->viewRenderer->setNoRender(true);

    		$outputHtml = $this->view->render('audio-job/assign-multiple-typist.phtml');
    		$outputStatus = 'ok';
    	}

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;
    	isset($outputHtml2) 		? $output['html2'] 			= $outputHtml2 		: null;
    	isset($outputHtml3) 		? $output['html3'] 			= $outputHtml3 		: null;
    	isset($outputHtml4) 		? $output['html4'] 			= $outputHtml4 		: null;
    	isset($outputHtml5) 		? $output['html5'] 			= $outputHtml5 		: null;
    	isset($outputCssBgcolor)	? $output['css_bgcolor'] 	= $outputCssBgcolor : null;

    	echo json_encode($output);
    }

    /**
     * Handle users that have been unassigned from split audio jobs
     * Ensure cancellation email is sent to the users
     *
     * @param Application_Model_AudioJobTypistMapper|Application_Model_AudioJobProofreaderMapper $model
     * @param int $audioJobId
     * @param array $currentAssignedUsers
     * @param array $newlyAssignedUsers
     *
     * @return void
     */
    protected function _removeUnassignedUsers($model, $audioJobId, $currentAssignedUsers, $newlyAssignedUsers, $emailTemplate)
    {
        $usersNotInList = array_diff($currentAssignedUsers, $newlyAssignedUsers);
        if (!empty($usersNotInList))
        {
            $subStandard = $this->getRequest()->getParam( 'substandard_payrate', null );
            foreach ($usersNotInList as $userId)
            {
                // Remove unselected users (assigned to audio job in db but not in new selected list)
                if (in_array($userId, $currentAssignedUsers))
                {
                    if (!in_array($userId, $newlyAssignedUsers) && !empty($userId))
                    {
                        // lookup the acl_role_id of the existing user and remove from acl for this object
                        $oldUser = $model->fetchByUserAndAudioJob($userId, $audioJobId);

                        $userData            = array();
                        $userData['id']      = $oldUser['id'];
                        $userData['current'] = 0;

                        if ( is_array( $subStandard ) && count( $subStandard ) > 0 )
                        {
                            foreach ( $subStandard as $subTypist )
                            {
                                if ( $subTypist == $oldUser['id'] )
                                {
                                    $userData['substandard_payrate'] = '1';
                                }
                            }
                        }

                        $model->save($userData);

                        $this->_insertAcl($audioJobId, $oldUser['acl_role_id'], 18, 'deny');
                        $this->_sendAssignedUpdateEmailNotification($emailTemplate, $oldUser['id']);
                    }
                }
            }
        }
    }

    /**
     * Handle users that have been assigned on a split audio job
     * Including updating the start/end duration of existing users
     *
     * @param Application_Model_AudioJobTypistMapper|Application_Model_AudioJobProofreaderMapper $model
     * @param int $audioJobId
     * @param array $currentAssignedUsers
     * @param array $newlyAssignedUsers
     *
     * @return void
     */
    protected function _addAssignedUsers($model, $audioJobId, $currentAssignedUsers, $newlyAssignedUsersData, $emailTemplate)
    {
        if (!empty($newlyAssignedUsersData))
        {
            // New typist
            foreach($newlyAssignedUsersData as $item)
            {
                // Split file with no user assigned to preserve the timings
                if (empty($item['user_id']))
                {
                    $item['user_id']  = 0;
                    $item['due_date'] = date('Y-m-d H:i:s');
                    $item['shift_id'] = 0;
                }

                $id = $model->save($item);

                // add user to acl resources for this object
                $userInfo = $model->fetchById($id);

                // Email notification for newly assigned users
                if (!in_array($userInfo['user_id'], $currentAssignedUsers) && !empty($userInfo['user_id']))
                {
                    $this->_insertAcl($audioJobId, $userInfo['acl_role_id'], 18, 'allow');
                    $this->_sendAssignedUpdateEmailNotification($emailTemplate, $id);
                }
            }
        }
    }

    /**
     * Typist/Proofreader assign audio job part / cancellation email notification
     *
     * @param string $emailTemplate
     * @param int    $audioJobsUserId (audio_jobs_typists|audio_jobs_proofreaders table id)
     *
     * @return void
     */
    protected function _sendAssignedUpdateEmailNotification($emailTemplate, $audioJobsUserId)
    {
        // email notification
        $options = array(
            'emailType' => $emailTemplate,
            'id'        => $audioJobsUserId
        );
        $this->_email->send($this->view, $options);
    }

    /**
     * Notify assigned typists/audio jobs of changes to audo job.
     * This is currently used to inform typists/proofreaders assigned to a split audio job
     * of changes to due dates.
     *
     * @param Application_Model_AudioJobProofreaderMapper|Application_Model_AudioTypistMapper $model
     * @param array $oldAssignedUsers
     * @param string $emailTemplate
     *
     * @return void
     */
    protected function _notifyUsersOfChanges($model, $oldAssignedUsers, $emailTemplate)
    {
        if (!empty($oldAssignedUsers))
        {
            foreach ($oldAssignedUsers as $olderUserData)
            {
                // Check that the due date has not changed
                $userInfo = $model->fetchById($olderUserData['id']);
                if (!empty($userInfo))
                {
                    if ($olderUserData['due_date'] !== $userInfo['due_date'] && !empty($userInfo['user_id']))
                    {
                        // Send email with changes
                        $options = array(
                            'emailType' => $emailTemplate,
                            'id'        => $userInfo['id']
                        );
                        $this->_email->send($this->view, $options);
                    }
                }
            }
        }
    }

    public function unsplitMultipleTypistAction()
    {
    	$output = array();

    	$form    = new Application_Form_AssignMultipleTypist();
    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

    	$audioJobId = $this->_request->getParam('audio_job_id');
    	if (!empty($audioJobId))
        {
            $audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();

            // Send cancellation notification to all current users
            $newlyAssignedUsers   = array();
            $currentAssignedUsers = $audioJobTypistMapper->fetchTypistList($audioJobId);
            $this->_removeUnassignedUsers($audioJobTypistMapper, $audioJobId, $currentAssignedUsers, $newlyAssignedUsers, 'audioJobTypistCancelled');

            // Remove all assigned typists
            $audioJobTypistMapper->unsplitAudioJob($audioJobId);
            $audioJobStatusId = 4;

            // set audio job status to assigned to typist
            $audioJobMapper                  = new Application_Model_AudioJobMapper();
            $audioJobMapperData              = array();
            $audioJobMapperData['id']        = $audioJobId;
            $audioJobMapperData['status_id'] = $audioJobStatusId;
            $audioJobMapper->save($audioJobMapperData);

            $outputHtmlTmp = $this->_makeTypistCellHtml($audioJobId, null, null, null, $audioJobStatusId);

            // typist cell html
            $outputHtml[$audioJobId] = $outputHtmlTmp[0];

            // set status html
            $outputHtml2[$audioJobId] = $outputHtmlTmp[1];

            // set typist due date
            $outputHtml3[$audioJobId] = $outputHtmlTmp[2];

            // set typist due date sort
            $outputHtml4[$audioJobId] = $outputHtmlTmp[3];

            // set transcript
            $outputHtml5[$audioJobId] = $outputHtmlTmp[4];

            // set row bg colour
            $outputCssBgcolor[$audioJobId] = $outputHtmlTmp[5];

            $outputStatus = 'ok';
    	}

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;
    	isset($outputHtml2) 		? $output['html2'] 			= $outputHtml2 		: null;
    	isset($outputHtml3) 		? $output['html3'] 			= $outputHtml3 		: null;
    	isset($outputHtml4) 		? $output['html4'] 			= $outputHtml4 		: null;
    	isset($outputHtml5) 		? $output['html5'] 			= $outputHtml5 		: null;
    	isset($outputCssBgcolor)	? $output['css_bgcolor'] 	= $outputCssBgcolor : null;

    	echo json_encode($output);
    }

    public function unsplitMultipleProofreaderAction()
    {
    	$output = array();

    	$form    = new Application_Form_AssignMultipleProofreader();
    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

    	$audioJobId = $this->_request->getParam('audio_job_id');
    	if (!empty($audioJobId))
        {
            $audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();

            // Send cancellation notification to all current users
            $newlyAssignedUsers   = array();
            $currentAssignedUsers = $audioJobProofreaderMapper->fetchProofreaderList($audioJobId);
            $this->_removeUnassignedUsers($audioJobProofreaderMapper, $audioJobId, $currentAssignedUsers, $newlyAssignedUsers, 'audioJobProofreaderCancelled');

            // Assign typist process
            $audioJobProofreaderMapper->unsplitAudioJob($audioJobId);
            $audioJobStatusId = 4;

            // set audio job status to assigned to typist
            $audioJobMapper = new Application_Model_AudioJobMapper();
            $audioJobMapperData = array();
            $audioJobMapperData['id'] = $audioJobId;
            $audioJobMapperData['status_id'] = $audioJobStatusId;
            $audioJobMapper->save($audioJobMapperData);

            $outputHtmlTmp = $this->_makeProofreaderCellHtml($audioJobId, null, null, null, $audioJobStatusId);

            // typist cell html
            $outputHtml[$audioJobId] = $outputHtmlTmp[0];

            // set status html
            $outputHtml2[$audioJobId] = $outputHtmlTmp[1];

            // set typist due date
            $outputHtml3[$audioJobId] = $outputHtmlTmp[2];

            // set typist due date sort
            $outputHtml4[$audioJobId] = $outputHtmlTmp[3];

            // set transcript
            $outputHtml5[$audioJobId] = $outputHtmlTmp[4];

            // set row bg colour
            //$outputCssBgcolor[$audioJobId] = $outputHtmlTmp[5];

            $outputStatus = 'ok';
    	}

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;
    	isset($outputHtml2) 		? $output['html2'] 			= $outputHtml2 		: null;
    	isset($outputHtml3) 		? $output['html3'] 			= $outputHtml3 		: null;
    	isset($outputHtml4) 		? $output['html4'] 			= $outputHtml4 		: null;
    	isset($outputHtml5) 		? $output['html5'] 			= $outputHtml5 		: null;
    	isset($outputCssBgcolor)	? $output['css_bgcolor'] 	= $outputCssBgcolor : null;

    	echo json_encode($output);
    }

    public function splitAudioJobAction()
    {
    	$output = array();

    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

    	$audioJobId = $this->_request->getParam('audio_job_id');
    	$this->view->audioJobId = $audioJobId;

    	$audioJobMapper = new Application_Model_AudioJobMapper();
    	$audioJob = $audioJobMapper->fetchById($audioJobId);
    	$lengthSeconds = $audioJob['length_seconds'];

    	$this->_helper->viewRenderer->setNoRender(true);

    	$typistCount = $this->_request->getParam('typist_count');

    	$data = $this->_makeMultipleTypistRows($lengthSeconds, $typistCount);

    	$this->view->data = $data;

    	$this->view->mode = 'edit';

    	$outputHtml = $this->view->render('audio-job/assign-multiple-typist.phtml');

    	$outputStatus = 'ok';

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;

    	echo json_encode($output);
    }

    public function splitAudioJobProofreaderAction()
    {
    	$output = array();

    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

    	$audioJobId = $this->_request->getParam('audio_job_id');
    	$this->view->audioJobId = $audioJobId;

    	$audioJobMapper = new Application_Model_AudioJobMapper();
    	$audioJob = $audioJobMapper->fetchById($audioJobId);
    	$lengthSeconds = $audioJob['length_seconds'];

    	$this->_helper->viewRenderer->setNoRender(true);

    	$proofreaderCount = $this->_request->getParam('proofreader_count');

    	$data = $this->_makeMultipleProofreaderRows($lengthSeconds, $proofreaderCount);

    	$this->view->data = $data;
    	$this->view->mode = 'edit';

    	$outputHtml = $this->view->render('audio-job/assign-multiple-proofreader.phtml');

    	$outputStatus = 'ok';

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;

    	echo json_encode($output);
    }

    private function _makeMultipleTypistRows($lengthSeconds, $typistCount) {
    	$mod = ($lengthSeconds) % 60;

   		$lengthSeconds = $lengthSeconds-$mod;

    	if ($mod != 0) {
    		$lengthSeconds += 60;
    	}

    	$lengthMinutes = $lengthSeconds/60;
    	$standardIncrement = $lengthMinutes/$typistCount;

    	// if standard increment is not a whole number then round down
		if (!is_int($standardIncrement)) {
			$standardIncrement = floor($standardIncrement);
		}

        $doBreak = false;
    	$data = array();
    	for($x=1; $x <= $typistCount; $x++) {
    		$dataTmp = array();
    		if ($x == 1) {
    			$timeStart = 0;
    		} else {
    			$timeStart = $timeEnd;
    		}

    		$timeEnd = ($timeStart + ($standardIncrement));

    		if ($timeEnd > $lengthMinutes) {
    			$timeEnd = $lengthMinutes;
    		}

    		if ($timeEnd == $lengthMinutes) {
    			$doBreak = true;
    		}
    		// add an extra minute to account for any minutes which were lost because $standardIncrement was rounded down
    		if ($typistCount == $x && $timeEnd < $lengthMinutes) {
    			$timeEnd += ($lengthMinutes - $timeEnd);
    		}

    		$dataTmp['audioJobIdTypistNumber'] = $x;
    		$dataTmp['minutes_start']          = $timeStart;
    		$dataTmp['minutes_end']            = $timeEnd;
            $dataTmp['shift_id']               = '';
            $dataTmp['typist_name']            = '';
            $dataTmp['due_date_unix']          = '';
    		$dataTmp['comment']                = '';
    		$dataTmp['id']                     = null;
    		$dataTmp['user_id']                = null;
    		$dataTmp['due_date']               = null;
            $dataTmp['accepted']               = false;
            $dataTmp['downloaded']             = false;
    		$data[$x]                          = $dataTmp;

    		if ($doBreak) {
    			break;
    		}
    	}

    	return $data;
    }

    private function _makeMultipleProofreaderRows($lengthSeconds, $proofreaderCount) {
    	$mod = ($lengthSeconds) % 60;

    	$lengthSeconds = $lengthSeconds-$mod;

    	if ($mod != 0) {
    		$lengthSeconds += 60;
    	}

    	$lengthMinutes = $lengthSeconds/60;
    	$standardIncrement = $lengthMinutes/$proofreaderCount;

    	// if standard increment is not a whole number then round down
		if (!is_int($standardIncrement)) {
			$standardIncrement = floor($standardIncrement);
		}

        $doBreak = false;
    	$data    = array();
    	for($x=1; $x <= $proofreaderCount; $x++) {
    		$dataTmp = array();
    		if ($x == 1) {
    			$timeStart = 0;
    		} else {
    			$timeStart = $timeEnd;
    		}

    		$timeEnd = $timeStart + ($standardIncrement);

			if ($timeEnd > $lengthMinutes) {
    			$timeEnd = $lengthMinutes;
    		}

    		if ($timeEnd == $lengthMinutes) {
    			$doBreak = true;
    		}
    		// add an extra minute to account for any minutes which were lost because $standardIncrement was rounded down
    		if ($proofreaderCount == $x && $timeEnd < $lengthMinutes) {
    			$timeEnd += ($lengthMinutes - $timeEnd);
    		}


			$dataTmp['audioJobIdProofreaderNumber'] = $x;
            $dataTmp['shift_id'] = '';
    		$dataTmp['minutes_start'] = $timeStart;
    		$dataTmp['minutes_end'] = $timeEnd;
    		$dataTmp['proofreader_name'] = '';
    		$dataTmp['due_date_unix'] = '';
    		$dataTmp['comment'] = '';
    		$dataTmp['id'] = null;
    		$dataTmp['user_id'] = null;
    		$dataTmp['due_date'] = null;
    		$data[$x] = $dataTmp;

    		if ($doBreak) {
    			break;
    		}
    	}

    	return $data;
    }

    private function _makeTypistCellHtml($audioJobId, $typistId, $dueDate, $audioJobsTypistsId, $statusId = 5) {
    	$data = array();
    	$outputHtml = array();
    	$audioJobMapper = new Application_Model_AudioJobMapper();

    	$status = $audioJobMapper->lookupStatusById($statusId);
    	$newStatus = $status['name'];

    	if (!empty($typistId) && !empty($dueDate) && !empty($audioJobsTypistsId)) {
	    	$users = new Application_Model_UserMapper();
	    	$user = $users->fetchById($typistId);
	    	$data['typist_name'] = $user['name'];
	    	$data['typist_due_date_unix'] = strtotime($dueDate);
	    	$data['typist_count'] = 1;
	    	$data['audio_jobs_typists_id'] = $audioJobsTypistsId;

	    	// set id element for use in the typist-list-cell template
	    	$data['id'] = $audioJobId;
	    	$data['accepted'] = 0;

            // Typist Due Date
            $data['typist_due_date'] = $dueDate;
	    	$this->view->data = $data;

	    	$this->_helper->viewRenderer->setNoRender(true);
	    	$outputHtml[0] = $this->view->render('audio-job/typist-list-cell.phtml');

	    	// make typist due date
	    	// now set up the data to be used in the ptml to generate the return html
	    	$currDateDte = new DateTime();
	    	$dueDateDte = new DateTime($dueDate);
	    	$interval = date_diff($currDateDte, $dueDateDte);

	    	// if due date is more than 24 hours away then do in days, else in hours
	    	if ($interval->d > 0) {
	    		$data['due_days'] = $dueDateDte <= $currDateDte ? -$interval->days : $interval->days;
	    		$data['due_hours'] = 0;
	    	} else {
	    		$data['due_days'] = 0;
	    		$data['due_hours'] = $dueDateDte <= $currDateDte ? -$interval->h : $interval->h;
	    	}

	    	$data['due_date_unix'] = $data['typist_due_date_unix'];

	    	$this->view->data = $data;
	    	$outputHtml[2] = $this->view->render('audio-job/typist-due-date-list-cell.phtml');
	    	$outputHtml[3] = $data['due_date_unix'];
    	} else {
    		$data['id'] = $audioJobId;

    		$audioJob = $audioJobMapper->fetchById($audioJobId);
    		$data['typist_count'] = $audioJob['typist_count'];
    		$this->view->data = $data;

    		$this->_helper->viewRenderer->setNoRender(true);
    		$outputHtml[0] = $this->view->render('audio-job/typist-list-cell.phtml');

    		// make typist due date
    		// now set up the data to be used in the ptml to generate the return html
    		$currDateDte = new DateTime();
    		$dueDateDte = new DateTime($audioJob['typist_due_date']);
    		$interval = date_diff($currDateDte, $dueDateDte);

    		// if due date is more than 24 hours away then do in days, else in hours
    		if ($interval->d > 0) {
    			$data['due_days'] = $dueDateDte <= $currDateDte ? -$interval->days : $interval->days;
    			$data['due_hours'] = 0;
    		} else {
    			$data['due_days'] = 0;
    			$data['due_hours'] = $dueDateDte <= $currDateDte ? -$interval->h : $interval->h;
    		}

    		$data['due_date_unix'] = $audioJob['typist_due_date_unix'];

    		$this->view->data = $data;

    		$outputHtml[2] = $this->view->render('audio-job/typist-due-date-list-cell.phtml');
    		$outputHtml[3] = $data['due_date_unix'];
    	}

    	// make status html
    	$outputHtml[1] = $this->view->escape($newStatus);

    	// set transcript
    	$data = array();
    	$data['id'] 						= $audioJobId;
    	$data['typist_count'] 				= 1;

    	$audioJob = $audioJobMapper->fetchById($audioJobId);

    	$data['transcription_file_count']	= $audioJob['transcription_file_count'];
//     	$data['transcription_file_id'] 		= $audioJob['transcription_file_id'];

    	// set upload permissions
    	if ($this->_identity->acl_group_id == 3) { // clients
    		$data['canUpload'] = false;
    	} else {
    		$data['canUpload'] = true;
    	}

    	$this->view->data = $data;
    	$outputHtml[4] = $this->view->render('audio-job/transcript-list-cell.phtml');

    	$outputHtml[5] = $this->_getRowbackgroundColour($audioJob);

    	return $outputHtml;
    }

    private function _makeProofreaderCellHtml($audioJobId, $proofreaderId, $dueDate, $audioJobsProofreadersId, $statusId = 9) {
    	$data = array();
    	$outputHtml = array();
    	$audioJobMapper = new Application_Model_AudioJobMapper();
        $audioJob = $audioJobMapper->fetchById($audioJobId);
    	$status = $audioJobMapper->lookupStatusById($statusId);
    	$newStatus = $status['name'];

    	if (!empty($proofreaderId) && !empty($dueDate) && !empty($audioJobsProofreadersId)) {
    		$users = new Application_Model_UserMapper();
    		$user = $users->fetchById($proofreaderId);
    		$data['proofreader_name'] = $user['name'];
    		$data['proofreader_due_date'] = strtotime($dueDate);

    		$data['proofreader_count'] = 1;
    		$data['audio_jobs_proofreaders_id'] = $audioJobsProofreadersId;

    		// set id element for use in the proofreader-list-cell template
    		$data['id'] = $audioJobId;
    		$data['accepted'] = 0;

    		// make proofreader due date
    		// now set up the data to be used in the ptml to generate the return html
    		$currDateDte = new DateTime();
    		$dueDateDte = new DateTime($dueDate);
    		$interval = date_diff($currDateDte, $dueDateDte);

    		// if due date is more than 24 hours away then do in days, else in hours
    		if ($interval->d > 0) {
    			$data['due_days'] = $dueDateDte <= $currDateDte ? -$interval->days : $interval->days;
    			$data['due_hours'] = 0;
    		} else {
    			$data['due_days'] = 0;
    			$data['due_hours'] = $dueDateDte <= $currDateDte ? -$interval->h : $interval->h;
    		}

    		$data['due_date_unix'] = $data['proofreader_due_date'];

    		$this->view->data = $data;

            $this->_helper->viewRenderer->setNoRender(true);
            $outputHtml[0] = $this->view->render('audio-job/proofreader-list-cell.phtml');

    		$outputHtml[2] = $this->view->render('audio-job/typist-due-date-list-cell.phtml');
    		$outputHtml[3] = $data['due_date_unix'];

    	} else {
    		$data['id'] = $audioJobId;
    		$data['proofreader_count'] = $audioJob['proofreader_count'];
    		$this->view->data = $data;

    		$this->_helper->viewRenderer->setNoRender(true);
    		$outputHtml[0] = $this->view->render('audio-job/proofreader-list-cell.phtml');

    		// make proofreader due date
    		// now set up the data to be used in the ptml to generate the return html
    		$currDateDte = new DateTime();
    		$dueDateDte = new DateTime($audioJob['proofreader_due_date']);
    		$interval = date_diff($currDateDte, $dueDateDte);

    		// if due date is more than 24 hours away then do in days, else in hours
    		if ($interval->d > 0) {
    			$data['due_days'] = $dueDateDte <= $currDateDte ? -$interval->days : $interval->days;
    			$data['due_hours'] = 0;
    		} else {
    			$data['due_days'] = 0;
    			$data['due_hours'] = $dueDateDte <= $currDateDte ? -$interval->h : $interval->h;
    		}

    		$data['due_date_unix'] = $audioJob['proofreader_due_date_unix'];

    		$this->view->data = $data;

    		$outputHtml[2] = $this->view->render('audio-job/typist-due-date-list-cell.phtml');
    		$outputHtml[3] = $data['due_date_unix'];

    	}

    	// make status html
    	$outputHtml[1] = $this->view->escape($newStatus);

    	$outputHtml[4] = $this->_getRowbackgroundColour($audioJob);

    	return $outputHtml;
    }

    public function acceptTypistAction()
    {
    	$output = array();
    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

		$id = $this->_request->getParam('id');
		$audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
		$data = array();
		$data['accepted'] = 1;
		$data['id'] = $id;

		$audioJobTypistMapper->save($data);

		$audioJobId = $this->_request->getParam('audio_job_id');

    	$this->_helper->viewRenderer->setNoRender(true);

    	// if the id of the current users is the same of the typist who is accepting the job then we want to use a
    	// different template
    	$audioJobMapper = new Application_Model_AudioJobMapper();
    	$audioTypist = $audioJobTypistMapper->fetchById($id);
    	if ($audioTypist['user_id'] == (int) $this->_identity->id) {
    		$audioJob = $audioJobTypistMapper->fetchAudioJobsByUserId((int) $this->_identity->id, $audioJobId);
    		$audioJob['typist_due_date'] = $audioJob['due_date_unix'];
    		$audioJob['audio_jobs_typists_id'] = $id;
    		$audioJob['id'] = $audioJobId;
    		$template = 'typist-list-cell-staff';
    	} else {
    		$audioJob = $audioJobMapper->fetchById($audioJobId);
    		// add in the audio_jobis_typist_id for the record being accepted
    		$audioJob['audio_jobs_typists_id'] = $id;
    		$audioJob['typist_due_date'] = $audioJob['typist_due_date_unix'];

    		$template = 'typist-list-cell';
    	}

    	$this->view->data = $audioJob;
    	$outputHtml = $this->view->render('audio-job/' . $template. '.phtml');

    	$outputStatus = 'ok';

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;
    	echo json_encode($output);
    }

    public function acceptProofreaderAction()
    {
    	$output = array();
    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

    	$id = $this->_request->getParam('id');
    	$audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
    	$data = array();
    	$data['accepted'] = 1;
    	$data['id'] = $id;

    	$audioJobProofreaderMapper->save($data);

    	// if the id of the current users is the same of the typist who is accepting the job then we want to use a
    	// different template
    	$audioJobId = $this->_request->getParam('audio_job_id');
    	$audioJobMapper = new Application_Model_AudioJobMapper();
    	$audioProofreader = $audioJobProofreaderMapper->fetchById($id);
    	if ($audioProofreader['user_id'] == (int) $this->_identity->id) {
    		$audioJob = $audioJobProofreaderMapper->fetchAudioJobsByUserId((int) $this->_identity->id, $audioJobId);
            $audiojob['due_date_unix'] = strtotime($audioJob['due_date']);
            $audioJob['proofreader_due_date'] = $audioJob['due_date'];
            $audioJob['accepted'] = 1;
    		$template = 'proofreader-list-cell-staff';
    	} else {
    		$audioJob = $audioJobMapper->fetchById($audioJobId);
    		// add in the audio_jobs_proofreader_id for the record being accepted
    		$audioJob['audio_jobs_proofreaders_id'] = $id;
    		$audioJob['proofreader_due_date'] = $audioJob['due_date_unix'] = $audioJob['proofreader_due_date_unix'];
            $audioJob['downloaded'] = 0;
            $audioJob['accepted']   = 1;
    		$template = 'proofreader-list-cell';
    	}

    	$this->view->data = $audioJob;

    	$this->_helper->viewRenderer->setNoRender(true);
    	$outputHtml = $this->view->render('audio-job/' . $template. '.phtml');

    	$outputStatus = 'ok';

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;
    	echo json_encode($output);
    }

    protected function _fetchShiftUserList( $shiftId, $name = null, $trainingCodes = array() )
    {
        $usersOnShift             = $this->_shiftMapper->fetchUsersOnShift( $shiftId, $name, $trainingCodes );
        $this->view->usersOnShift = $usersOnShift;
        $this->view->otherUsers   = $this->_shiftMapper->fetchUsersNotOnShift( $shiftId, $name, $trainingCodes );
    }

    protected function _populateSelectedShiftDropdown($form, $shiftId)
    {
        if (!empty($shiftId))
        {
            $shift = $this->_defaultShiftMapper->fetchCurrentShift($shiftId);
            $form->getElement('shift_day')->setValue($shift['start_day_number']);
            $shiftTimes = $this->_defaultShiftMapper->getShiftTimesForDropdown($shift['start_day_number']);
            $form->getElement('shift_time')->addMultiOptions($shiftTimes);
            $form->getElement('shift_time')->setValue($shift['id']);
        }
        return $form;
    }

    public function assignMultipleProofreaderAction()
    {
    	$output = array();

    	$form    = new Application_Form_AssignMultipleProofreader();
    	$this->view->hasTopMenu = false;
    	$this->view->layout()->disableLayout();

    	$data = array();
    	if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();

    		//     		if ($form->isValid($this->getRequest()->getPost())) {
    		$audioJobId = $this->_request->getParam('audio_job_id');

    		foreach($this->_request->getParams() as $key => $param)
            {
    			if (substr($key, 0, 12) == 'proofreader-') {

    				$keyValues = explode('-', $key);

    				if (!array_key_exists($keyValues[2], $data)) {
    					$data[$keyValues[2]] = array();
    					$data[$keyValues[2]]['audio_job_id'] = $keyValues[1];
    					$data[$keyValues[2]][$keyValues[3]] = $param;
    				} else {
    					$data[$keyValues[2]][$keyValues[3]] = $param;
    				}
    			}
    		}

    		$audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();

            if (!empty($data))
            {
                $currentAssignedUsers = $audioJobProofreaderMapper->fetchProofreaderList($audioJobId, true);
                $proofreaderList      = $audioJobProofreaderMapper->fetchByAudioJobId($audioJobId);
                $newlyAssignedUsers   = array();
                foreach ($data as $item)
                {
                    if (!empty($item['user_id']))
                    {
                        $newlyAssignedUsers[] = $item['user_id'];
                    }
                    else
                    {
                        // No user selected for one of the parts - set satus to Part assigned
                        $audioJobStatusId = 28;
                    }
                }
                $this->_removeUnassignedUsers($audioJobProofreaderMapper, $audioJobId, $currentAssignedUsers, $newlyAssignedUsers, 'audioJobProofreaderCancelled');
                $this->_addAssignedUsers($audioJobProofreaderMapper, $audioJobId, $currentAssignedUsers, $data, 'audioJobAssignedToProofreader');
                $this->_notifyUsersOfChanges($audioJobProofreaderMapper, $proofreaderList, 'audioJobProofreaderDeadlineChanges');
            }

    		$audioJobId = $this->_request->getParam('audio_job_id');
    		// set audio job status to assigned to proofreader
    		$audioJobMapper = new Application_Model_AudioJobMapper();
    		$audioJobMapperData = array();
    		$audioJobMapperData['id'] = $audioJobId;
    		$audioJobMapperData['status_id'] = 9;
    		$audioJobMapper->save($audioJobMapperData);

    		$outputHtmlTmp = $this->_makeProofreaderCellHtml($audioJobId, null, null, null);

    		// proofreader cell html
    		$outputHtml[$audioJobId] = $outputHtmlTmp[0];

    		// set status html
    		$outputHtml2[$audioJobId] = $outputHtmlTmp[1];

    		// set proofreader due date
    		$outputHtml3[$audioJobId] = $outputHtmlTmp[2];

    		// set row bg colour
    		$outputCssBgcolor[$audioJobId] = $outputHtmlTmp[3];

    		$outputStatus = 'ok';

    	} else {

    		$audioJobId = $this->_request->getParam('audio_job_id');
    		$this->view->audioJobId = $audioJobId;

    		$mode = $this->_request->getParam('mode');

    		if ($mode == 'edit' || $mode == 'addRow') {
    			$audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
    			$data = $audioJobProofreaderMapper->fetchByAudioJobId($audioJobId);

    			// if adding a new row then get the start and end times for the all the rows (including the existing ones and new one being added)
    			if ($mode =='addRow') {
    				$audioJobMapper = new Application_Model_AudioJobMapper();
    				$audioJob = $audioJobMapper->fetchById($audioJobId);
    				$lengthSeconds = $audioJob['length_seconds'];

    				$proofreaderCount = $this->_request->getParam('row_count');
    				$rowData = $this->_makeMultipleProofreaderRows($lengthSeconds, $proofreaderCount+1);
    			}

    			// add a unique row id
    			$x = 1;
    			foreach ($data as &$item) {
    				$item['audioJobIdProofreaderNumber'] = $x;
    				if ($mode == 'addRow') {
    					$item['minutes_start'] = $rowData[$x]['minutes_start'];
    					$item['minutes_end'] = $rowData[$x]['minutes_end'];
    				}
    				$x++;
    			}

    			// add new element to data array for new row
    			if ($mode == 'addRow') {
    				for ($y = $x; $y <= $proofreaderCount; $y++) {
    					$data[] = array(
    		    				'audioJobIdProofreaderNumber' 	=> $y,
    		    				'due_date'	 		=> null,
    		    				'minutes_start'	 	=> $rowData[$y]['minutes_start'],
    		    				'minutes_end'	 	=> $rowData[$y]['minutes_end'],
    							'proofreader_name'	=> '',
    							'due_date_unix' 	=> '',
    							'comment' 			=> '',
    							'id'				=> null,
    							'user_id'			=> null,
    							'due_date'			=> null,
    					);
    				}
    			}

    			$this->view->data = $data;
    		}

    		$this->view->mode = $mode;
    		$this->view->form = $form;

    		$this->_helper->viewRenderer->setNoRender(true);

    		$outputHtml = $this->view->render('audio-job/assign-multiple-proofreader.phtml');
    		$outputStatus = 'ok';
    	}

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;
    	isset($outputHtml2) 		? $output['html2'] 			= $outputHtml2 		: null;
    	isset($outputHtml3) 		? $output['html3'] 			= $outputHtml3 		: null;
    	isset($outputCssBgcolor)	? $output['css_bgcolor'] 	= $outputCssBgcolor : null;

    	echo json_encode($output);
    }

    public function updatePriorityAction()
    {
    	$output = array();
    	$outputHtml = array();

    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	$audioJobMapper = new Application_Model_AudioJobMapper();

    	$priorityId = $this->_request->getParam('priority_id');

    	// lookup the standard data we'll need for creating the priority html snippet
    	$priorityMapper = new Application_Model_PriorityMapper();
    	$priority = $priorityMapper->fetchById($priorityId);
    	$data = array();

    	$data['priority_id'] = $priorityId == 0 ? null : $priorityId;

    	$htmlData['priority_flag_colour'] 	= $priority['flag_colour'];
    	$htmlData['priority_name'] 			= $priority['name'];
    	$htmlData['priority_sort_order'] 	= $priority['sort_order'];

    	if ($this->_request->getParam('use_multiple_audio_job_ids') == 'true') {
    		foreach($this->_request->getParams() as $key => $param) {

    			if (substr($key, 0, 6) == 'check-') {
    				$data['id'] = $param;
    				$audioJobMapper->save($data);
    				// unset the audio job id ready for the next loop iteration
    				unset($data['id']);

    				$htmlData = array_merge($htmlData, $data);
    				$this->view->data = $htmlData;
    				$outputHtml[$param] = $this->view->render('audio-job/priority-list-cell.phtml');
    			}
    		}
    	} else {
    		$id = $this->_request->getParam('audio_job_id');
    		$data['id'] = $id;

    		$audioJobMapper->save($data);

    		$htmlData = array_merge($htmlData, $data);
    		$this->view->data = $htmlData;
    		$outputHtml[$id] = $this->view->render('audio-job/priority-list-cell.phtml');

    	}

    	$outputStatus = 'ok';

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;
    	echo json_encode($output);
    }

    /**
     * Update due dates
     *
     * @return void
     */
    public function updateDueDateAction()
    {
    	$output = array();

    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	$audioJobMapper = new Application_Model_AudioJobMapper();

    	$dueDate = $this->_request->getParam('due_date');
        $id      = $this->_request->getParam('id');
        $idArray = explode('-', $id);

        $date       = new DateTime($dueDate);
        $dateFormat = $date->format('Y-m-d H:i:s');

        foreach ($idArray as $audioJobId)
        {
            $data = array(
                'id'                     => $audioJobId,
                'client_due_date'        => $dateFormat,
                'manual_client_due_date' => '1'
            );
            $audioJobMapper->save($data);
        }

        $message = 'You have edited ' . count($idArray) . ' audio jobs';
        $this->flashMessenger->addMessage(array('notice' => $message));

    	$output['status'] = 'ok';
    	echo json_encode($output);
    }

    /**
     * Update typist due date
     *
     * @return void
     */
    public function updateTypistDueDateAction()
    {
        $output = array();

        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $typistAudioJobMapper = new Application_Model_AudioJobTypistMapper();

        $dueDate = $this->_request->getParam('due_date');
        $id      = $this->_request->getParam('id');
        $idArray = explode('-', $id);

        $date       = new DateTime($dueDate);
        $dateFormat = $date->format('Y-m-d H:i:s');

        $audioJobsChanged = 0;
        $typistsChanged   = 0;

        foreach ($idArray as $audioJobId)
        {
            $currentTypists = $typistAudioJobMapper->fetchByAudioJobId($audioJobId);
            if (!empty($currentTypists))
            {
                $typistsChanged += $typistAudioJobMapper->update(
                    array('due_date' => $dateFormat),
                    "audio_job_id ='" . $audioJobId . "' AND current = '1'"
                );
                if ($typistsChanged > 0)
                {
                    $audioJobsChanged++;
                }
                foreach ($currentTypists as $row)
                {
                    if ($row['due_date'] !== $dateFormat)
                    {
                        $options = array(
                            'emailType' => 'audioJobTypistDeadlineChanges',
                            'id'        => $row['id']
                        );
                        $this->_email->send($this->view, $options);
                    }
                }
            }
        }
        $message = $audioJobsChanged . ' audio job(s) changed, ' . $typistsChanged . ' typist deadline dates changed';
        $this->flashMessenger->addMessage(array('notice' => $message));
        $output['status'] = 'ok';
        echo json_encode($output);
    }

    /**
     * Update proofreader due date
     *
     * @return void
     */
    public function updateProofreaderDueDateAction()
    {
        $output = array();

        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $typistAudioJobMapper = new Application_Model_AudioJobProofreaderMapper();

        $dueDate = $this->_request->getParam('due_date');
        $id      = $this->_request->getParam('id');
        $idArray = explode('-', $id);

        $date       = new DateTime($dueDate);
        $dateFormat = $date->format('Y-m-d H:i:s');

        $audioJobsChanged = 0;
        $typistsChanged   = 0;

        foreach ($idArray as $audioJobId)
        {
            $currentTypists = $typistAudioJobMapper->fetchByAudioJobId($audioJobId);
            if (!empty($currentTypists))
            {
                $typistsChanged += $typistAudioJobMapper->update(
                    array('due_date' => $dateFormat),
                    "audio_job_id ='" . $audioJobId . "' AND current = '1'"
                );
                foreach ($currentTypists as $row)
                {
                    if ($row['due_date'] !== $dateFormat)
                    {
                        $options = array(
                            'emailType' => 'audioJobProofreaderDeadlineChanges',
                            'id'        => $row['id']
                        );
                        $this->_email->send($this->view, $options);
                    }
                }

                if ($typistsChanged > 0)
                {
                    $audioJobsChanged++;
                }
            }
        }
        $message = $audioJobsChanged . ' audio job(s) amended, ' . $typistsChanged . ' proofreader deadline date(s) amended';
        $this->flashMessenger->addMessage(array('notice' => $message));
        $output['status'] = 'ok';
        echo json_encode($output);
    }

    public function updateStatusAction()
    {
    	$output = array();
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	$audioJobMapper = new Application_Model_AudioJobMapper();

    	$statusId = $this->_request->getParam('status_id');
    	$id       = $this->_request->getParam('id');
        $idArray  = explode('-', $id);

        $success = array();
        $error   = array();

        $groupIds = array();

        $statusMapper = new Application_Model_AudioJobStatusMapper();
        $newStatus    = $statusMapper->fetchRow( 'id = ' . $statusId );

        foreach ($idArray as $audioJobId)
        {
            $audioJob = $audioJobMapper->fetchById($audioJobId);
            $status   = $statusMapper->fetchRow( 'id = ' . $audioJob['status_id'] );

            $data     = array(
                'id'        => $audioJobId,
                'status_id' => $statusId
            );

            if ( ( '1' == $newStatus['typist_editable'] || '1' == $newStatus['proofreader_editable']) &&  ( '1' != $status['typist_editable'] || '1' != $status['proofreader_editable'] ) )
            {
                $data['last_status_id'] = $status['id'];
            }
            else
            {
                $data['completed'] = $status['complete'];
            }

            $result = $audioJobMapper->save($data);

            if (!is_array($result))
            {
                $groupIds[$audioJob['job_id']][] = $audioJobId;
                $success[]                       = $audioJobId;
            }
            else
            {
                $error[] = $audioJobId;
            }
        }

        if (Application_Model_AudioJobStatusMapper::STATUS_APPROVED == $statusId)
        {
            if (count($success) > 0)
            {
                foreach ($groupIds as $audioJobs)
                {
                    $options = array(
                        'emailType' => 'audioJobApproved',
                        'id'		=> $audioJobs
                    );
                    $this->_email->send($this->view, $options);
                }
            }
        }
        elseif (Application_Model_AudioJobStatusMapper::STATUS_CANCELLED == $statusId)
        {
            if (count($success) > 0)
            {
                $typistMapper      = new Application_Model_AudioJobTypistMapper();
                $proofreaderMapper = new Application_Model_AudioJobProofreaderMapper();

                foreach ($groupIds as $audioJobs)
                {
                    foreach ($audioJobs as $audioJobId)
                    {
                        $typists      = $typistMapper->fetchByAudioJobId($audioJobId);
                        $proofreaders = $proofreaderMapper->fetchByAudioJobId($audioJobId);

                        foreach ($typists as $typistRow)
                        {
                            $options = array(
                                'emailType' => 'audioJobTypistCancelled',
                                'id'        => $typistRow['id']
                            );
                            $this->_email->send($this->view, $options);
                        }

                        foreach ($proofreaders as $proofreaderRow)
                        {
                            $options = array(
                                'emailType' => 'audioJobProofreaderCancelled',
                                'id'        => $proofreaderRow['id']
                            );
                            $this->_email->send($this->view, $options);
                        }
                    }
                }
            }
        }
        elseif (Application_Model_AudioJobStatusMapper::STATUS_RETURNED == $statusId)
        {
            if (count($success) > 0)
            {
                $jobMapper = new Application_Model_JobMapper();

                foreach ($groupIds as $audioJobs)
                {
                    foreach ($audioJobs as $audioJobId)
                    {
                        $options = array(
                            'emailType' => 'audioJobReturnedToClient',
                            'id'		=> $audioJobId
                        );
                        $this->_email->send($this->view, $options);
                        if (!$jobMapper->hasEmailEachTranscriptOnComplete($audioJob['job_id']))
                        {
                            $audioJob       = $audioJobMapper->fetchById($audioJobId);
                            $completedCount = $audioJobMapper->fetchCompletedCountByJobId($audioJob['job_id']);
                            $count          = $audioJobMapper->fetchCountByJobId($audioJob['job_id']);
                            if ($completedCount === $count)
                            {
                                break;
                            }
                        }
                    }
                }
            }
        }

        $successMessage = count($success) . ' audio job(s) status updated';
        $this->_flash->addMessage(array('notice' => $successMessage));
        if (count($error) > 0)
        {
            $errorMessage = count($error) . ' audio job(s) could not be updated';
            $this->_flash->addMessage(array('error' => $errorMessage));
        }
    	$output['status'] = 'ok';

    	echo json_encode($output);
    }



    private function _getRowbackgroundColour($audioJob) {
		// workout the background colour of the parent row
    	// set default to blank string
    	$parentRowBackgroundColour = '';
    	if ($audioJob['status_id'] == 1) {
    		$parentRowBackgroundColour = '#CEE3C0';
    	}

    	if (!is_null($audioJob['client_due_date_unix']) && $audioJob['client_due_date_unix'] <= time()) {
    		$parentRowBackgroundColour = '#FED1D2';
    	}

    	return $parentRowBackgroundColour;
    }

    public function listAction()
    {
    	$canEdit = $this->_acl->isAccessAllowed($this->_identity->id,'audio-job','edit');
    	$this->view->audioJobCanEdit = $canEdit;

    	$audioJobCanArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'audio-job','archive');
    	$this->view->audioJobCanArchive = $audioJobCanArchive;

    	$canUpload = $this->_acl->isAccessAllowed($this->_identity->id,'transcription-file','upload');
    	$this->view->canUpload = $canUpload;

    	$audioJobMapper = new Application_Model_AudioJobMapper();
     	$jobStatuses = $audioJobMapper->fetchAllStatusesForDropdown();
    	$this->view->audioJobStatuses = $jobStatuses;

    	$filter = $this->_request->getParam('filter', 'all');
    	switch ($filter) {
    		case 'all':
    			$audioJobs = $audioJobMapper->fetchCurrent(null, null);
    			break;
			case 'pending':
				$audioJobs = $audioJobMapper->fetchByStatus(1, null);
    			break;
    		case 'today':
    			$date = new DateTime();
    			$dateFrom = $date->format('Y-m-d 00:00:00');
     			$dateTo = $date->format('Y-m-d 23:59:59');
    			$audioJobs = $audioJobMapper->fetchByDateRange($dateFrom, $dateTo, null);
    			break;
    		case 'tomorrow':
    			$date = new DateTime();
    			$dateFrom = $date->add(new DateInterval('P1D'))->format('Y-m-d 00:00:00');
    			$dateTo = $date->add(new DateInterval('P1D'))->format('Y-m-d 23:59:59');
    			$audioJobs = $audioJobMapper->fetchByDateRange($dateFrom, $dateTo, null);
    			break;
			case 'week':
    			$date = new DateTime();
    			$dateFrom = $date->format('Y-m-d 00:00:00');
    			$dateTo = $date->add(new DateInterval('P6D'))->format('Y-m-d 23:59:59');
				$audioJobs = $audioJobMapper->fetchByDateRange($dateFrom, $dateTo, null);
    			break;
            case 'overdue':
                $audioJobs = $audioJobMapper->fetchCurrent(null, null, 'aj.client_due_date < NOW()');
                break;
            case 'no-typist';
                $audioJobs = $audioJobMapper->fetchCurrent(null, null, null, 'typist_count = "0"');
                break;
            case 'no-proofreader':
                $audioJobs = $audioJobMapper->fetchCurrent(null, null, null, 'proofreader_count = "0"');
                break;
            case 'staff-statuses':
                $audioJobs = $audioJobMapper->fetchCurrent(null, null, 'lajs.typist_editable = "1" OR lajs.proofreader_editable = "1"');
                break;
    	}

        $audioJobs = $audioJobMapper->populatePrices( $audioJobs, true );

        if (count($audioJobs) > 0)
        {
            array_walk( $audioJobs, array( $this, 'addCanEdit' ), $canEdit );
            array_walk( $audioJobs, array( $this, 'addCanUpload' ), $canUpload );
            array_walk( $audioJobs, array( $this, 'addIsLead' ) );
            array_walk( $audioJobs, array( $this, 'hasSubStandardLiabilities' ) );
        }

    	$this->view->audioJobs         = $audioJobs;
        $this->view->suppressLeadClass = true;

        $this->view->linkLeads = true;

    	$this->render('list-for-job');
    }

    public function addCanEdit(&$itemArray, $key, $canEdit)
    {
    	$itemArray['canEdit'] = $canEdit;
    }

    public function addCanUpload(&$itemArray, $key, $canUpload)
    {
    	$itemArray['canUpload'] = $canUpload;
    }

    public function hasSubStandardLiabilities( &$itemArray )
    {
        $audioJobTypistMapper        = new Application_Model_AudioJobTypistMapper();
        $itemArray['hasSubStandard'] = $audioJobTypistMapper->hasSubStandardLiabilities( $itemArray['id'] );
    }

    public function addIsLead(&$itemArray)
    {
        $audioJobMapper      = new Application_Model_AudioJobMapper;
        $itemArray['isLead'] = $audioJobMapper->isLead($itemArray['id']);
    }

    public function addUserIsLead(&$itemArray)
    {
        $audioJobMapper      = new Application_Model_AudioJobMapper;
        $itemArray['isLead'] = $audioJobMapper->isLead($itemArray['audio_job_id']);
    }

    public function listTypistAction()
    {
        $unassigned = (bool) (int) $this->getRequest()->getParam( 'unassigned', false );

    	$chkCurrent = $this->_request->getParam('chk_current', null);
    	if (is_null($chkCurrent))
        {
    		$chkCurrent = true;
    		$this->view->chkCurrent = true;
    	}
        else
        {
    		$this->view->chkCurrent = (bool)$chkCurrent;
    	}

    	$chkCompleted = $this->_request->getParam('chk_completed', null);
    	if (is_null($chkCompleted))
        {
    		$chkCompleted = false;
    		$this->view->chkCompleted = false;
    	}
        else
        {
    		$this->view->chkCompleted = (bool) $chkCompleted;
    	}

    	$canEdit             = false;
    	$this->view->canEdit = $canEdit;

        if ( !$unassigned )
        {
    	    $canUpload             = $this->_acl->isAccessAllowed($this->_identity->id,'transcription-file','upload');
            $supportFileCanArchive = $this->_acl->isAccessAllowed($this->_identity->id,'support-file','archive');

            $audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
            $audioJobs = $audioJobTypistMapper->fetchAudioJobsByUserId((int) $this->_identity->id, null, $chkCurrent, $chkCompleted);

            $view = 'list-for-job-typist';
        }
        else
        {
            $canUpload             = false;
            $supportFileCanArchive = false;

            $where = 'typist_count = "0" AND status_id IN("'
                . Application_Model_AudioJobStatusMapper::STATUS_UNASSIGNED
                . '")';

            $audioJobMapper = new Application_Model_AudioJobMapper();
            $audioJobs      = $audioJobMapper->fetchCurrent(
                null,
                null,
                null,
                $where,
                false
            );

            $view = 'list-for-job-unassigned';
        }

        $this->view->supportFileCanArchive = $supportFileCanArchive;
        $this->view->canUpload             = $canUpload;

    	// add canEdit info to $audioJobs so can pass into partialLoop
    	array_walk($audioJobs, array($this, 'addCanEdit'), $canEdit);

    	// add canUpload info to $audioJobs so can pass into partialLoop
    	array_walk($audioJobs, array($this, 'addCanUpload'), $canUpload);

        // Order by Leads
        array_walk($audioJobs, array($this, 'addUserIsLead'));

    	$this->view->audioJobs = $audioJobs;

        //var_dump( $audioJobs ); die;

    	$this->render( $view );
    }

    public function listProofreaderAction()
    {
        $unassigned = (bool) (int) $this->getRequest()->getParam( 'unassigned', false );
    	$chkCurrent = $this->_request->getParam('chk_current', null);

    	if (is_null($chkCurrent)) {
    		$chkCurrent = true;
    		$this->view->chkCurrent = true;
    	} else {
    		$this->view->chkCurrent = (bool)$chkCurrent;
    	}

    	$chkCompleted = $this->_request->getParam('chk_completed', null);
    	if (is_null($chkCompleted)) {
    		$chkCompleted = false;
    		$this->view->chkCompleted = false;
    	} else {
    		$this->view->chkCompleted = (bool)$chkCompleted;
    	}

    	$canEdit = false;
    	$this->view->canEdit = $canEdit;

        if ( !$unassigned )
        {
            $canUpload             = $this->_acl->isAccessAllowed($this->_identity->id,'transcription-file','upload');
            $supportFileCanArchive = $this->_acl->isAccessAllowed($this->_identity->id,'support-file','archive');

            $audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
            $audioJobs                 = $audioJobProofreaderMapper->fetchAudioJobsByUserId((int) $this->_identity->id,  null, $chkCurrent, $chkCompleted);

            $view = 'list-for-job-proofreader';
        }
        else
        {
            $canUpload             = false;
            $supportFileCanArchive = false;

            $where = 'proofreader_count = "0" AND status_id IN("'
                . Application_Model_AudioJobStatusMapper::STATUS_COMPLETED_NEEDS_PR
                . '")';

            $audioJobMapper = new Application_Model_AudioJobMapper();
            $audioJobs      = $audioJobMapper->fetchCurrent(
                null,
                null,
                null,
                $where,
                false
            );

            $view = 'list-for-job-unassigned';
        }

        $this->view->canUpload             = $canUpload;
        $this->view->supportFileCanArchive = $supportFileCanArchive;

    	// add canEdit info to $audioJobs so can pass into partialLoop
    	array_walk($audioJobs, array($this, 'addCanEdit'), $canEdit);

    	// add canUpload info to $audioJobs so can pass into partialLoop
    	array_walk($audioJobs, array($this, 'addCanUpload'), $canUpload);

        // Order by Leads
        array_walk($audioJobs, array($this, 'addUserIsLead'));

    	$this->view->audioJobs = $audioJobs;
    	$this->render( $view );
    }

    public function confirmDownloadTypistAction()
    {
    	// disable layout and view
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // action body
        $id = $this->_request->getParam('id');

        // record the download for this user
        $audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
        $audioTypist = $audioJobTypistMapper->fetchById($id);
//         $audioTypist = $audioJobTypistMapper->fetchRowByAudioJobIdCurrentUserIdTypistIsCurrent($id);


        // if we have record then the current user is one of the assigned typists so need to update audio_jobs_typists to show they have downloaded the record
        if ($audioTypist) {
        	$data = array();
        	$data['id'] = $audioTypist['id'];
        	$data['downloaded'] = 1;
        	$audioJobTypistMapper->save($data);

    		$data = $audioJobTypistMapper->fetchById($audioTypist['id']);

    		$data['typist_due_date'] = $data['due_date_unix'];

    		// add in the audio_jobis_typist_id for the record being accepted
    		$this->view->data = $data;

    	   	$this->_helper->viewRenderer->setNoRender(true);
    	   	$outputHtml = $this->view->render('audio-job/typist-list-cell-staff.phtml');
        } else {
        	// just return blank html
        	$outputHtml = '';
        }

        $outputStatus = 'ok';

        $output['status'] = $outputStatus;
        $output['html'] = $outputHtml;

        echo json_encode($output);
    }

    public function confirmDownloadProofreaderAction()
    {
        // disable layout and view
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // action body
        $id = $this->_request->getParam('id');

        // record the download for this user
        $audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
        $audioProofreader = $audioJobProofreaderMapper->fetchById($id);

        // if we have record then the current user is one of the assigned typists so need to update audio_jobs_typists to show they have downloaded the record
        if ($audioProofreader)
        {
            $data = array();
            $data['id'] = $audioProofreader['id'];
            $data['downloaded'] = 1;
            $audioJobProofreaderMapper->save($data);

            $data = $audioJobProofreaderMapper->fetchById($audioProofreader['id']);

            $data['proofreader_due_date']       = $data['due_date_unix'];
            $data['audio_jobs_proofreaders_id'] = $data['user_id'];

            // add in the audio_jobs_proofreader_id for the record being accepted
            $this->view->data = $data;

            $this->_helper->viewRenderer->setNoRender(true);
            if (null === $this->_request->getParam('type', null))
            {
                $outputHtml = $this->view->render('audio-job/proofreader-list-cell-staff.phtml');
            }
            else
            {
                $outputHtml = $this->view->render('audio-job/proofreader-list-cell.phtml');
            }
        } else {
            // just return blank html
            $outputHtml = '';
        }

        $outputStatus = 'ok';

        $output['status'] = $outputStatus;
        $output['html'] = $outputHtml;

        echo json_encode($output);
    }


    public function downloadAction()
    {
        ini_set('memory_limit', '2048M');
    	// disable layout and view
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	// action body
    	$id = $this->_request->getParam('id');

    	// record the download
    	$audioJobDownloadMapper = new Application_Model_AudioJobDownloadMapper();
    	$data = array();
    	$data['audio_job_id'] = $id;
    	$audioJobDownloadMapper->save($data);

    	$audioJobMapper = new Application_Model_AudioJobMapper();
    	$audioJob = $audioJobMapper->fetchById($id);

    	header('Content-Type: ' . $audioJob['mime_type']);
    	header('Content-Disposition: attachment; filename="' . $audioJob['file_name'] . '"');
        header("Pragma: public");
        header("Cache-Control: public");
    	readfile(APPLICATION_PATH.'/../data/' . $audioJob['id']);
    }

    public function typistPanicAction()
    {
    	// disable layout and view
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	// action body
    	$id = $this->_request->getParam('id');
    	$comment = html_entity_decode($this->_request->getParam('comment', null));

    	// record the comment
    	$audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
    	$data = array();
    	$data['id'] = $id;
    	$data['panic_comment'] = $comment;
    	$audioJobTypistMapper->save($data);

    	// email notification
    	$options = array(
    		'emailType' => 'audioJobTypistPanic',
    		'id'		=> $id
    	);
    	$this->_email->send($this->view, $options);

    	$outputStatus = 'ok';

    	$output['status'] = $outputStatus;
    	echo json_encode($output);
    }

    public function proofreaderPanicAction()
    {
    	// disable layout and view
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	// action body
    	$id = $this->_request->getParam('id');
    	$comment = html_entity_decode($this->_request->getParam('comment', null));

    	// record the comment
    	$audioJobProofreaderMapper = new Application_Model_AudioJobProofreaderMapper();
    	$data = array();
    	$data['id'] = $id;
    	$data['panic_comment'] = $comment;
    	$audioJobProofreaderMapper->save($data);

    	// email notification
    	$options = array(
        		'emailType' => 'audioJobProofreaderPanic',
        		'id'		=> $id
    	);
    	$this->_email->send($this->view, $options);

    	$outputStatus = 'ok';

    	$output['status'] = $outputStatus;
    	echo json_encode($output);
    }

    protected function _insertAcl($audioJobId, $aclRoleId, $aclPrivilegeId, $mode='deny')
    {

    	$audioJobMapper = new Application_Model_AudioJobMapper();
    	$audioJob = $audioJobMapper->fetchById($audioJobId);

    	$aclResourceId = $audioJob['acl_resource_id'];
    	$aclMapper = new Application_Model_AclMapper();

		$existing = $aclMapper->fetchByResourceIdRoleIdAndPrivilege($aclResourceId, $aclRoleId, $aclPrivilegeId);
    	foreach ($existing as $acl) {
    		$data = array();
    		$data['id'] = $acl['id'];
    		$data['mode'] = $mode;
    		$aclMapper->save($data);
    	}

    	// belt and braces -
    	// if we've not updated any acl records previous loop then do it here
    	if (count($existing) == 0) {
	    	$aclData = array(
	    		'role_id'      => $aclRoleId,
	    		'resource_id'  => $aclResourceId,
	    		'privilege_id' => $aclPrivilegeId,
	    		'mode'         => $mode
	    	);
	    	$aclMapper->insert($aclData);
    	}
    }


}