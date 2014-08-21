<?php

class JobController extends App_Controller_Action
{
	private $uploads = '/../data/tmp/';

    /**
     * Client ID
     * @var int
     */
    protected $_clientId;

    public function init()
    {
    	$this->_email = $this->_helper->getHelper('email');
        if( Zend_Auth::getInstance()->hasIdentity() )
        {
            $this->_identity = Zend_Auth::getInstance()->getIdentity();
            if( '3' == $this->_identity->acl_group_id )
            {
                $clientMapper = new Application_Model_ClientsUserMapper;
                $client       = $clientMapper->fetchByUserId( $this->_identity->id );
                if( $client )
                {
                    $this->_clientId = $client['id'];
                }
            }
        }
        parent::init();
    }

	public function archiveAction()
	{
		$this->view->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$id = $this->_request->getParam('id');

		$canArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','archive');
		if ($canArchive) {
			if (!empty($id)) {
				$mapper  = new Application_Model_JobMapper();
				$data = array();
				$data['id']      = $id;
				$data['deleted'] = date( 'Y-m-d H:i:s' );
				$id = $mapper->save($data);

				// set all related audio job to completed
				$audioJobMapper = new Application_Model_AudioJobMapper();
				$audioJobs = $audioJobMapper->fetchNonArchived($id);
				foreach ($audioJobs as $audioJob) {
					$audioJobData = array();
					$audioJobData['id'] = $audioJob['id'];
					$audioJobData['deleted'] = date( 'Y-m-d H:i:s' );
					$audioJobMapper->save($audioJobData);
				}

				$output['status'] = 'ok';
			} else {
				$output['status'] = 'fail';
			}
		} else {
			$output['status'] = 'fail';
		}
		echo json_encode($output);
	}

    public function indexAction()
    {
    }

    public function updatePriorityAction()
    {
    	$output = array();
    	$outputHtml = array();

    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	$jobMapper = new Application_Model_JobMapper();

    	$priorityId = $this->_request->getParam('priority_id');

    	// lookup the standard data we'll need for creating the priority html snippet
    	$priorityMapper = new Application_Model_PriorityMapper();
    	$priority = $priorityMapper->fetchById($priorityId);
    	$data = array();

    	$data['priority_id'] = $priorityId == 0 ? null : $priorityId;

    	$htmlData['priority_flag_colour'] 	= $priority['flag_colour'];
    	$htmlData['priority_name'] 			= $priority['name'];
    	$htmlData['priority_sort_order'] 	= $priority['sort_order'];

    	if ($this->_request->getParam('use_multiple_job_ids') == 'true') {
    		foreach($this->_request->getParams() as $key => $param) {

    			if (substr($key, 0, 6) == 'check-') {
    				$data['id'] = $param;
    				$jobMapper->save($data);
    				// unset the audio job id ready for the next loop iteration
    				unset($data['id']);

    				$htmlData = array_merge($htmlData, $data);
    				$this->view->data = $htmlData;
    				$outputHtml[$param] = $this->view->render('audio-job/priority-list-cell.phtml');
    			}
    		}
    	} else {
    		$id = $this->_request->getParam('job_id');
    		$data['id'] = $id;

    		$jobMapper->save($data);

    		$htmlData = array_merge($htmlData, $data);
    		$this->view->data = $htmlData;
    		$outputHtml[$id] = $this->view->render('audio-job/priority-list-cell.phtml');

    	}

    	$outputStatus = 'ok';

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;
    	echo json_encode($output);
    }

    public function updateDueDateAction()
    {
    	$output = array();
    	$outputHtml = array();

    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	$jobMapper = new Application_Model_JobMapper();

    	$dueDate = $this->_request->getParam('due_date');

    	$canEdit = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','edit');

    	// now set up the data to be used in the ptml to generate the return html
    	$currDateDte = new DateTime();
    	$dueDateDte = new DateTime($dueDate);

    	$interval = date_diff($currDateDte, $dueDateDte);

    	if ($this->_request->getParam('use_multiple_job_ids') == 'true') {
    		foreach($this->_request->getParams() as $key => $param) {
    			$data = array();
    			if (substr($key, 0, 6) == 'check-') {
    				$data['id'] = $param;
    				$data['job_due_date'] = $dueDate;
    				$jobMapper->save($data);

    				// if due date is more than 24 hours away then do in days, else in hours
    				if ($interval->d > 0) {
		    			$data['due_days'] = $dueDateDte <= $currDateDte ? -$interval->days : $interval->days;
		    			$data['due_hours'] = 0;
		    		} else {
		    			$data['due_days'] = 0;
		    			$data['due_hours'] = $dueDateDte <= $currDateDte ? -$interval->h : $interval->h;
		    		}

    				$data['due_date_unix'] = date_timestamp_get($dueDateDte);
    				$data['canEdit'] = $canEdit;

    				$this->view->data = $data;
    				$outputHtml[$param] = $this->view->render('audio-job/due-date-list-cell.phtml');

    				$job = $jobMapper->fetchById($id);
    				$outputCssBgColour[$id] = $this->_getRowbackgroundColour($job);
    			}
    		}
    	} else {
    		$id = $this->_request->getParam('job_id');
    		$data = array();
    		$data['id'] = $id;
    		$data['job_due_date'] = $dueDate;

    		$jobMapper->save($data);

    		// if due date is more than 24 hours away then do in days, else in hours
    		if ($interval->d > 0) {
    			$data['due_days'] = $dueDateDte <= $currDateDte ? -$interval->days : $interval->days;
    			$data['due_hours'] = 0;
    		} else {
    			$data['due_days'] = 0;
    			$data['due_hours'] = $dueDateDte <= $currDateDte ? -$interval->h : $interval->h;
    		}
    		$data['due_date_unix'] = date_timestamp_get($dueDateDte);
    		$data['canEdit'] = $canEdit;

    		$this->view->data = $data;
    		$outputHtml[$id] = $this->view->render('audio-job/due-date-list-cell.phtml');

    		$job = $jobMapper->fetchById($id);
    		$outputCssBgColour[$id] = $this->_getRowbackgroundColour($job);

    	}

    	$outputStatus = 'ok';

    	$output['status'] = $outputStatus;
    	$output['html'] = $outputHtml;
    	$output['css_bgcolor'] = $outputCssBgColour;
    	echo json_encode($output);
    }

    public function updateStatusAction()
    {
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	$jobMapper      = new Application_Model_JobMapper();
        $audioJobMapper = new Application_Model_AudioJobMapper();

    	$statusId    = $this->_request->getParam('status_id');
        $jobIdString = $this->_request->getParam('job_id');
        $jobIds      = explode('-', $jobIdString);

        $status = $jobMapper->fetchStatus($statusId);

        $success  = array();
        $error    = array();

        foreach ($jobIds as $jobId)
        {
            $data = array(
                'id'        => $jobId,
                'status_id' => $statusId
            );
            if ('1' == $status['complete'])
            {
                $audioJobs = $audioJobMapper->countNonArchivedAndNonCompleted($jobId);
                if (0 < $audioJobs)
                {
                    $error[] = $jobId;
                }
                else
                {
                    if ( '1' == $status['invoiced'] )
                    {
                        $data['invoiced_date'] = date("Y-m-d H:i:s");
                    }
                    $success[] = $jobId;
                    $jobMapper->save($data);
                }
            }
            else
            {
                $success[] = $jobId;
                $jobMapper->save($data);
            }
        }

        if ($statusId === Application_Model_JobMapper::STATUS_COMPLETED && 0 < count($success))
        {
            foreach($success as $jobId)
            {
                $options = array(
                    'emailType' => 'jobCompleted',
                    'id'		=> $jobId
                );
                $this->_email->send($this->view, $options);
            }
        }

        $successMessage = count($success) . ' project(s) updated to status: "' . $status['name'] . '"';
        $this->_flash->addMessage(array('notice' => $successMessage));
        if (count($error) > 0)
        {
            $errorMessage = count($error) . ' project(s) could not be updated';
            $this->_flash->addMessage(array('error' => $errorMessage));
        }

        $output = array(
            'status' => 'ok'
        );

    	echo json_encode($output);
    }

	private function _getRowbackgroundColour($job) {
		// workout the background colour of the parent row
    	// set default to blank string
    	$parentRowBackgroundColour = '';
    	if ($job['status_id'] == 1) {
    		$parentRowBackgroundColour = '#CEE3C0';
    	}

    	if (!is_null($job['job_due_date_unix']) && $job['job_due_date_unix'] <= time()) {
    		$parentRowBackgroundColour = '#FED1D2';
    	}

    	return $parentRowBackgroundColour;
    }

    public function uploadAction()
    {
    	$this->_helper->layout->setLayout('upload-layout');

    	if ($this->_request->isPost()) {
    		// disable layout and view
    		$this->view->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
    		$this->upload();
    		exit;
    	}

    	if ($this->_request->isGet())
        {
    		$this->view->hasTopMenu = false;
    		$jobId                  = $this->_request->getParam('id');

            $jobMapper       = new Application_Model_JobMapper();
            $job             = $jobMapper->fetchRow( $jobMapper->select()->where( 'id = ?', $jobId ) );
            $this->view->job = $job;

            $clientMapper = new Application_Model_ClientMapper();
            $client       = $clientMapper->fetchRow( 'id = ' . $job->client_id );

            $services   = App_Db_Manipulate::convertToDropDown( $client->getAllServices() );
            $this->view->services = $services;

            $speakerNumbers  = array();
            $turnaroundTimes = array();
            $service         = false;

            if ( !empty( $job->service_id ) )
            {
                $serviceMapper = new Application_Model_Service();
                $service       = $serviceMapper->fetchRow( 'id = ' . $job->service_id );

                $speakerNumbers  = $service->getServiceSpeakerNumbersDropDown();
                $turnaroundTimes = $service->getServiceTurnaroundTimesDropDown();
            }

            $this->view->service = $service;

    		$this->view->userId = $this->_identity->id;

    		$this->view->speakerNumbers = $speakerNumbers;
            $this->view->turnaroundTimes = $turnaroundTimes;

    		$audioFileQualityMapper = new Application_Model_AudioFileQualityMapper();
    		$audioFileQualities = $audioFileQualityMapper->fetchAllForDropdown();
    		$this->view->audioFileQualities = $audioFileQualities;

    		// create js warning script for file quality
    		$warningJs = '';
    		foreach($audioFileQualities as $quality) {
    			if (!empty($quality['warning'])) {
    				$warningJs .= 'if ($(this).val() == ' . $quality['key'] . ') {alert(\'' . $quality['warning'] . '\')}';
    			}
    		}
    		$this->view->audioFileQualitiesWarningJs = $warningJs;
            $this->view->audioJobs = $this->_getAudioJobsByJobId($jobId);


    		// create js warning script for turnaround times
    		$warningJs = '';
    		$warningJs .= 'if ($(this).val() == 4 && moment().format(\'H\') > 9) {alert(\'For same day we usually need the audio by 10am, please contact the office on 0207 928 1048 and we will see if we can accommodate your request. Alternatively please select either the overnight or 24 hour option.\')}';
    		$warningJs .= 'if ($(this).val() == 5 && moment().format(\'H\') > 17 ) {alert(\'We usually need the audio by 7pm for overnight work. If you have arranged later upload with the office please continue. If not please select either the 24 hour or same day options or email bookings@takenotetyping.com and we will do our best to accommodate your request.\')}';

    		$this->view->turnaroundTimeWarningJs = $warningJs;
    	}
    }

    /**
     * Get other audio jobs of the same job
     *
     * @param int $jobId Job ID
     *
     * @return Zend_Db_Table_Rowset
     */
    protected function _getAudioJobsByJobId($jobId)
    {
        if ($this->_acl->isAdmin())
        {
            $jobModel = new Application_Model_AudioJobMapper;
            return $jobModel->fetchByJobId($jobId);
        }
    }

    public function reuploadAction()
    {
    	$this->_helper->layout->setLayout('upload-layout');
    	if ($this->_request->isPost()) {
    		// disable layout and view
    		$this->view->layout()->disableLayout();
    		$this->_helper->viewRenderer->setNoRender(true);
    		$this->_reupload();
    		exit;
    	}
    }

    public function changeRequestAction()
    {

    	$output = array();
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	$form    = new Application_Form_JobChangeRequest();

    	if ($this->getRequest()->isPost()) {

    		if ($form->isValid($this->getRequest()->getPost())) {

    			$data = $form->getValues();
    			$jobMapper  = new Application_Model_JobChangeRequestMapper();
    			$id = $jobMapper->save($data);

    			// send email notification
    			$options = array(
    					'emailType' => 'jobChangeRequest',
    	    			'id' => $id
    			);
    			$this->_email->send($this->view, $options);

    			$output['status'] = 'ok';

    			$html = '<div class="popup-content"><p>Thank you.</p><p>Your request has been despatched to our administration team</p>';
    			$html .= '<a href="#" onclick="$(\'#job-dialog-change-request\').dialog(\'close\'); return false;" class="button-img">
    			<img src="' . $this->view->baseUrl() .'/images/close_button.png" title="Close">
    			</a></div>';
    			$output['html'] = $html;
    			echo json_encode($output);

    		} else {
    			$this->view->form = $form;

    			$outputHtml= $this->view->render('job/change-request.phtml');

    			$outputStatus = 'invalid';

    			$output['html'] = $outputHtml;
    			$output['status'] = $outputStatus;
    			echo json_encode($output);
    		}
    	} else {
    		$id = $this->_request->getParam('id');
    		$jobMapper = new Application_Model_JobMapper();
    		$job = $jobMapper->fetchById($id);
    		$this->view->job = $job;

    		$form->setDefault('job_id', $id);
    		$this->view->form = $form;

    		$outputHtml= $this->view->render('job/change-request.phtml');

    		$outputStatus = 'ok';

    		$output['html'] = $outputHtml;
    		$output['status'] = $outputStatus;
    		echo json_encode($output);
    	}

    }

    /**
     * Ad-hoc action
     *
     * @return void
     */
    public function adHocAction()
    {
        $output = array();
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
        $jobId  = $this->_request->getParam('job_id');
        $mapper = new Application_Model_AdHocMapper;
        $form   = new Application_Form_AdHocCharge;
        $form->setDefault('job_id', $jobId);
        $outputStatus = 'ok';
        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();
            if ($form->isValid($postData))
            {
                $mapper->insert($postData);
            }
            else
            {
                $outputStatus = '';
            }

        }
        $jobMapper           = new Application_Model_JobMapper();
        $job                 = $jobMapper->fetchById($jobId);
        $this->view->job     = $job;
        $this->view->form    = $form;
        $this->view->current = $mapper->getServicesByJob($jobId);
        $outputHtml          = $this->view->render('job/ad-hoc.phtml');
        $output['html']      = $outputHtml;
        $output['status']    = $outputStatus;
        echo json_encode($output);

        $jobId = $this->_request->getParam('id');
    }

    /**
     * Delete adhoc
     *
     * @return void
     */
    public function deleteAdHocAction()
    {
        $output = array();
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
        $outputStatus = '';
        if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();
            $mapper   = new Application_Model_AdHocMapper;
            $result   = $mapper->deleteAdHoc($postData['job_id']);
            if (false !== $result)
            {
                $outputStatus = 'ok';
            }
        }
        $output['status'] = $outputStatus;
        echo json_encode($output);
    }

    public function linkCreateAction()
    {

    	$output = array();
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	if ($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();
            $numForms = trim($postData['num_files']);
            if (empty($numForms) || !is_numeric($numForms))
            {
                $numForms = 1;
            }

            $formData = array();
            for ($c = 0; $c < $numForms; $c++)
            {
                if (0 === $c)
                {
                    $formData[] = array(
                        'job_id'                => $postData['job_id'],
                        'link'                  => $postData['link-url'],
                        'file_name'             => ((empty($postData['file_name'])) ? 'Audio Job - ' . $postData['job_id'] . ' - 1': $postData['file_name']),
                        'length_hours'          => $postData['length_hours'],
                        'length_mins'           => $postData['length_mins'],
                        'service_id'            => $postData['service_id'],
                        'turnaround_time_id'    => $postData['turnaround_time_id'],
                        'audio_quality_id'      => $postData['audio_quality_id'],
                        'speaker_numbers_id'    => $postData['speaker_numbers_id'],
                        'client_comments'       => $postData['client_comments'],
                        'is_exact'              => $postData['is_exact'],
                        'price_modifiers'       => $postData['price_modifiers']
                    );
                }
                else
                {
                    $formData[] = array(
                        'job_id'                => $postData['job_id'],
                        'link'                  => $postData['link-url'],
                        'file_name'             => ((empty($postData['file_name-' . $c])) ? 'Audio Job - ' . $postData['job_id'] . ' - ' . ($c + 1) : $postData['file_name-' . $c]),
                        'length_hours'          => $postData['length_hours-' . $c],
                        'length_mins'           => $postData['length_mins-' . $c],
                        'service_id'            => $postData['service_id-' . $c],
                        'turnaround_time_id'    => $postData['turnaround_time_id-' . $c],
                        'audio_quality_id'      => $postData['audio_quality_id-' . $c],
                        'speaker_numbers_id'    => $postData['speaker_numbers_id-' . $c],
                        'client_comments'       => $postData['client_comments'],
                        'is_exact'              => $postData['is_exact-' . $c],
                        'price_modifiers'       => $postData['price_modifiers-' . $c]
                    );
                }
            }

            //var_dump( $formData ); die;

            $invalid = false;
            foreach ($formData as $dataArray)
            {
                $form = new Application_Form_AudioJobLinkCreate();

                if (!$form->isValid($dataArray))
                {
                    $invalid = true;
                }
            }

            if (false === $invalid)
            {
                $mapper       = new Application_Model_AudioJobMapper();
                $statusMapper = new Application_Model_AudioJobStatusMapper();
                $status       = $statusMapper->fetchBySortOrder('1');

                foreach ($formData as $dataArray)
                {
                    $dataArray['length_seconds']  = $mapper->converTimeToSeconds($dataArray['length_hours'], $dataArray['length_mins']);
                    unset($dataArray['length_hours']);
                    unset($dataArray['length_mins']);
                    if ('no' === $dataArray['is_exact'] || 1 > $dataArray['length_seconds'])
                    {
                        $dataArray['status_id'] = $status['id'];
                    }
                    else
                    {
                        $dataArray['status_id'] = 1;
                    }
                    unset($dataArray['is_exact']);
                    $id = $mapper->save($dataArray);

                    // Set access for shared colleagues
                    $this->_shareAudioJobAccess($postData['job_id'], $id);

                    // send email notification
                    $options = array(
                        'emailType' => 'audioJobLinkReceived',
                        'id' => $id
                    );
                    $this->_email->send($this->view, $options);
                }
                $output['status'] = 'ok';
	    		echo json_encode($output);
            }
            else
            {
                $this->view->invalid = true;
                $this->_linkCreateDisplayForm($numForms, $postData);
    		}
    	}
        else
        {
            $this->_linkCreateDisplayForm();
    	}
    }

    /**
     *
     */
    protected function _linkCreateDisplayForm($numForms = 1, $formData = null)
    {
        $output          = array();
        $jobId           = $this->_request->getParam('id');
        $jobMapper       = new Application_Model_JobMapper();
        $job             = $jobMapper->fetchRow( $jobMapper->select()->where( 'id = ?', $jobId ) );
        $this->view->job = $job;

        $clientMapper = new Application_Model_ClientMapper();
        $client       = $clientMapper->fetchRow( 'id = ' . $job->client_id );
        $form         = new Application_Form_AudioJobLinkCreate();

        $emptyValue = array( 0 => '-- Select --' );
        $services   = App_Db_Manipulate::convertToDropDown( $client->getAllServices(), $emptyValue );
        $form->getElement( 'service_id' )->addMultiOptions( $services );

        $speakerNumbers  = $emptyValue;
        $turnAroundTimes = $emptyValue;
        $service         = false;

        if ( !empty( $job->service_id ) )
        {
            $serviceMapper = new Application_Model_Service();
            $service       = $serviceMapper->fetchRow( 'id = ' . $job->service_id );

            $speakerNumbers  = $emptyValue + $service->getServiceSpeakerNumbersDropDown();
            $turnAroundTimes = $emptyValue + $service->getServiceTurnaroundTimesDropDown();
        }

        $this->view->service = $service;

        $form->getElement( 'speaker_numbers_id' )->addMultiOptions( $speakerNumbers );
        $form->getElement( 'turnaround_time_id' )->addMultiOptions( $turnAroundTimes );

        $form->setDefault('job_id', $jobId);
        $form->setDefault('speaker_numbers_id', $job['speaker_numbers_id']);
        $form->setDefault('audio_quality_id', $job['audio_quality_id']);
        $form->setDefault('turnaround_time_id', $job['turnaround_time_id']);
        $form->setDefault('service_id', $job['service_id']);

        $this->view->form = $form;

        // create js warning script for file quality
        // Note: we do this here because we need to check from the db which audio quality elements need to have a warning applied to them
        $audioFileQualityMapper = new Application_Model_AudioFileQualityMapper();
        $audioFileQualities     = $audioFileQualityMapper->fetchAllForDropdown();

        $warningJs = '';
        foreach($audioFileQualities as $quality)
        {
            if (!empty($quality['warning']))
            {
                $warningJs .= 'if ($("#audio_quality_id").val() == ' . $quality['key'] . ') {alert(\'' . $quality['warning'] . '\')}';
            }
        }
        $this->view->audioFileQualitiesWarningJs = 'function checkAudioQualityWarnings(){' . $warningJs . '}';

        $warningJs  = '';
        $warningJs .= 'if ($("#turnaround_time_id").val() == 4 && moment().format(\'H\') > 9) {alert(\'For same day we usually need the audio by 10am, please contact the office on 0207..... and we will see if we can accommodate your request. Alternatively please select either the overnight or 24 hour option.\')}';
        $warningJs .= 'if ($("#turnaround_time_id").val() == 5 && moment().format(\'H\') > 17 ) {alert(\'We usually need the audio by 7pm for overnight work. If you have arranged later upload with the office please continue. If not please select either the 24 hour or same day options or email bookings@takenotetyping.com and we will do our best to accommodate your request.\')}';

        $this->view->turnaroundTimeWarningJs = 'function checkTurnaroundTimeWarnings(){' . $warningJs . '}';
        $this->view->numForms  = $numForms;
        $this->view->formData  = $formData;
        $outputHtml       = $this->view->render('job/link-create.phtml');
        if (null === $formData)
        {
            $outputStatus = 'ok';
        }
        else
        {
            $outputStatus = 'invalid';
        }
        $output['html']   = $outputHtml;
        $output['status'] = $outputStatus;
        echo json_encode($output);
    }

    /**
     * Fetch client service details ajax for link form
     *
     * @return void
     */
    public function fetchServiceDetailsLinkAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $serviceId = $this->getRequest()->getParam( 'service_id', null );
        $jobId     = $this->getRequest()->getParam( 'job_id', null );

        $output = array(
            'turnaround_times' => '-',
            'speaker_numbers'  => '-'
        );

        if ( $serviceId > 0 )
        {
            $form       = new Application_Form_AudioJobLinkCreate();
            $emptyValue = array( 0 => '-- Select --' );

            $serviceMapper = new Application_Model_Service();
            $service       = $serviceMapper->fetchRow( 'id = ' . $serviceId );

            $speakerNumbers  = $emptyValue + $service->getServiceSpeakerNumbersDropDown();
            $turnAroundTimes = $emptyValue + $service->getServiceTurnaroundTimesDropDown();


            $jobMapper = new Application_Model_JobMapper();
            $job       = $jobMapper->fetchRow( $jobMapper->select()->where( 'id = ?', $jobId ) );

            $form->getElement( 'speaker_numbers_id' )->addMultiOptions( $speakerNumbers );
            $form->setDefault( 'speaker_numbers_id', $job->speaker_numbers_id );
            $form->getElement( 'turnaround_time_id' )->addMultiOptions( $turnAroundTimes );
            $form->setDefault( 'turnaround_time_id', $job->turnaround_time_id );

            $output['speaker_numbers']  = (string) $form->speaker_numbers_id;
            $output['turnaround_times'] = (string) $form->turnaround_time_id;
            $output['modifiers']        = $this->view->partial( 'job/_partials/_priceModifiers.phtml', 'default', array( 'service' => $service ) );
        }

        echo json_encode( $output );
    }

    /**
     * Fetch service details for changed service in upload form
     *
     * @return void
     */
    public function fetchServiceDetailsUploadAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $serviceId     = $this->getRequest()->getParam( 'service_id', null );
        $jobId         = $this->getRequest()->getParam( 'job_id', null );

        $serviceMapper = new Application_Model_Service();
        $jobMapper     = new Application_Model_JobMapper();

        $service = $serviceMapper->fetchRow( 'id = ' . $serviceId );
        $job     = $jobMapper->fetchRow( 'id = ' . $jobId );

        $turnaroundTimes = $service->getServiceTurnaroundTimesDropDown();
        $speakerNumbers  = $service->getServiceSpeakerNumbersDropDown();

        $output = array(
            'speaker_numbers'  => $this->view->partial( 'job/_partials/upload/_speakerNumbers.phtml', 'default', array( 'job' => $job, 'speakerNumbers' => $speakerNumbers ) ),
            'turnaround_times' => $this->view->partial( 'job/_partials/upload/_turnaroundTimes.phtml', 'default', array( 'job' => $job, 'turnaroundTimes' => $turnaroundTimes ) ),
            'modifiers'        => $this->view->partial( 'job/_partials/upload/_priceModifiers.phtml', 'default',  array( 'service' => $service ) )
        );

        echo json_encode( $output );

    }

    public function listAction()
    {
    	// action body
    	$canEdit = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','edit');
    	$this->view->canEdit = $canEdit;
    	$canArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','archive');
    	$this->view->canArchive = $canArchive;

    	$jobMapper = new Application_Model_JobMapper();

    	$jobs = $jobMapper->fetchCurrent('due_days');
    	// set permissions for actions on jobs
    	array_walk($jobs, array($this, 'addCanEdit'), $canEdit);

    	$this->view->jobs = $jobs;

    	$this->view->filter = 'Current';
    	$jobStatuses = $jobMapper->fetchAllStatusesForDropdown();
    	$this->view->jobStatuses = $jobStatuses;

    	$priorityMapper = new Application_Model_PriorityMapper();
    	$jobPriorities = $priorityMapper->fetchAllForDropdown();
    	$this->view->jobPriorities = $jobPriorities;
    }

    public function listCompletedAction()
    {
    	// action body
    	$canEdit = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','edit');
    	$this->view->canEdit = $canEdit;
    	$canArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','archive');
    	$this->view->canArchive = $canArchive;

    	$jobMapper = new Application_Model_JobMapper();

    	$jobs = $jobMapper->fetchCompleted( 'due_days', true );
    	// set permissions for actions on jobs
    	array_walk($jobs, array($this, 'addCanEdit'), $canEdit);

    	$this->view->jobs = $jobs;

    	$this->view->filter = 'Completed';

    	$jobStatuses = $jobMapper->fetchAllStatusesForDropdown();
    	$this->view->jobStatuses = $jobStatuses;

    	$priorityMapper = new Application_Model_PriorityMapper();
    	$jobPriorities = $priorityMapper->fetchAllForDropdown();
    	$this->view->jobPriorities = $jobPriorities;

    	$this->render('list');
    }

    public function listInvoicedAction()
    {
        // action body
        $canEdit = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','edit');
        $this->view->canEdit = $canEdit;
        $canArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','archive');
        $this->view->canArchive = $canArchive;

        $jobMapper = new Application_Model_JobMapper();

        $pending = $this->getRequest()->getParam( 'pending', null );

        if ( '1' == $pending )
        {
            $jobs = $jobMapper->fetchPendingInvoice('due_days');
        }
        else
        {
            $jobs = $jobMapper->fetchInvoiced('due_days');
        }

        // set permissions for actions on jobs
        array_walk($jobs, array($this, 'addCanEdit'), $canEdit);

        $this->view->jobs = $jobs;

        $this->view->filter = 'Completed';

        $jobStatuses = $jobMapper->fetchAllStatusesForDropdown();
        $this->view->jobStatuses = $jobStatuses;

        $priorityMapper = new Application_Model_PriorityMapper();
        $jobPriorities = $priorityMapper->fetchAllForDropdown();
        $this->view->jobPriorities = $jobPriorities;

        $this->render('list');
    }

    public function listArchivedAction()
    {
        $jobMapper = new Application_Model_JobMapper();
        $jobs      = $jobMapper->fetchArchived();

        $this->view->suppressInitSort = true;
        $this->view->jobs             = $jobs;
        $this->view->filter           = 'Archived';

        $this->render('list');
    }

    public function listClientAction()
    {
    	// action body
    	$canEdit = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','edit');
        $this->view->canEdit = $canEdit;

    	$jobMapper = new Application_Model_JobMapper();

    	$jobs = $jobMapper->fetchCurrent('due_days');
    	// set permissions for actions on jobs
    	array_walk($jobs, array($this, 'addCanEdit'), $canEdit);

    	$this->view->jobs = $jobs;
    	$this->view->filter = 'Current';
    }

    public function listClientCompletedAction()
    {
    	// action body
    	$canEdit = false;
    	$this->view->canEdit = $canEdit;

    	$jobMapper = new Application_Model_JobMapper();

    	$jobs = $jobMapper->fetchCompleted( 'due_days' );

    	// set permissions for actions on jobs
    	array_walk($jobs, array($this, 'addCanEdit'), $canEdit);

    	$this->view->jobs = $jobs;
    	$this->view->filter = 'Completed';
    	$this->render('list-client');

    }

    function addCanEdit(&$itemArray, $key, $canEdit)
    {
    	$itemArray['canEdit'] = $canEdit;
    }

    function addCanUpload(&$itemArray, $key, $canUpload)
    {
    	$itemArray['canUpload'] = $canUpload;
    }

    function supportFileCanArchive(&$itemArray, $key, $supportFileCanArchive)
    {
    	$itemArray['supportFileCanArchive'] = $supportFileCanArchive;
    }

    public function hasSubStandardLiabilities( &$itemArray )
    {
        $audioJobTypistMapper        = new Application_Model_AudioJobTypistMapper();
        $itemArray['hasSubStandard'] = $audioJobTypistMapper->hasSubStandardLiabilities( $itemArray['id'] );
    }

    public function addIsLead(&$itemArray)
    {
        $audioJobMapper = new Application_Model_AudioJobMapper;
        $children = $audioJobMapper->fetchByLeadIdBasic($itemArray['id']);
        $itemArray['isLead'] = (bool) $children;
        if ((bool) $children)
        {
            $childrenArray = array();
            foreach ($children as $child)
            {
                $childrenArray[] = $child['id'];
            }
            $itemArray['linkedFiles'] = $childrenArray;
        }
    }

	public function createAction()
    {

    	// action body
        $request = $this->getRequest();
        if ($this->_acl->isAdmin())
        {
        	$this->view->requestUrl = '\\en\\default\\job\\list';
        }
        elseif($this->_identity->acl_group_id == 3)
        {
        	$this->view->requestUrl = '\\en\\default\\job\\list-client';
        }

        $form          = new Application_Form_JobCreate();
        $isShareAccess = false;
        if ($this->getRequest()->isPost())
        {
            $formData = $this->getRequest()->getPost();

        	// if an admin is using the create job form then the primary_user_id field
        	// is populated by ajax so contents aren't known to Zend Form. So, if client_id is popluated then we populate
        	// the primary_user_id field at this point so that it isn't empty if the form fails validation
        	$form->populate($request->getPost());

            // Add users granted access to the additional recipients
            if (isset($formData['shareaccess']))
            {
                $isShareAccess = true;
            }

            $additionalTranscriptRecipients = $formData['additional_transcript_recipients'];

        	$foo = $form->getElement('client_id');
            $clientId = null;
        	if ($foo->getValue() != 0) {
        		$clientsUserMapper = new Application_Model_ClientsUserMapper();
    			$data = $clientsUserMapper->fetchByClientIdForDropdown($foo->getValue());

        		$foo2 = $form->getElement('primary_user_id');
        		$foo2->addMultiOptions($data);
                $clientId = $foo->getValue();
        	}

            $data = $formData;

        	if ($form->isValid($data))
            {
        		// set default statusId
        		$data['status_id'] = 1;

        		// remove potentially null values
        		if (isset($data['speaker_numbers_id']) && $data['speaker_numbers_id'] == 0) {
        			unset($data['speaker_numbers_id']);
        		}

        		if (isset($data['audio_quality_id']) && $data['audio_quality_id'] == 0) {
         			unset($data['audio_quality_id']);
        		}

        		if (isset($data['turnaround_time_id']) && $data['turnaround_time_id'] == 0) {
         			unset($data['turnaround_time_id']);
        		}

        		if (isset($data['service_id']) && $data['service_id'] == 0) {
         			unset($data['service_id']);
        		}

                if ( isset( $data['price_modifiers'] ) )
                {
                    $data['modifiers'] = serialize( $data['price_modifiers'] );
                    unset( $data['price_modifiers'] );
                }

                $data['additional_transcript_recipients'] = $additionalTranscriptRecipients;

                if ( isset( $data['shareaccess'] ) )
                {
                    unset( $data['shareaccess'] );
                }

                $mapper  = new Application_Model_JobMapper();
        		$id = $mapper->save($data);

                // Grant/remove access to primary user colleagues
                $shareAccessList = array();
                if ($isShareAccess)
                {
                    $shareAccessList = $formData['shareaccess'];
                }

                $this->_shareJobAccess($id, $formData['client_id'], $formData['primary_user_id'], $shareAccessList);

                if ($id > 0) {
        			$this->_helper->FlashMessenger('Record Saved!');
        		} else {
        			$this->_helper->FlashMessenger(array('error' => 'Record Not Saved!'));
        		}

         		return $this->_helper->redirector->gotoSimple('view', 'job', 'default', array('id' => $id));

        	}
            else
            {
                if ( $data['client_id'] > 0 )
                {
                    $clientMapper         = new Application_Model_ClientMapper();
                    $client               = $clientMapper->fetchRow( 'id = ' . $data['client_id'] );
                    $this->view->services = $client->getAllServices();

                    if ( $data['service_id'] )
                    {
                        $serviceMapper = new Application_Model_Service();
                        $service       = $serviceMapper->fetchRow( 'id = ' . $data['service_id'] );
                        $this->view->service = $service;
                    }
                    if ( $data['turnaround_time_id'] > 0 )
                    {
                        $this->view->turnaround_time_id = $data['turnaround_time_id'];
                    }

                    if ( $data['speaker_numbers_id'] > 0 )
                    {
                        $this->view->speaker_numbers_id = $data['speaker_numbers_id'];
                    }
                }
        			$this->_helper->FlashMessenger(array('error' => 'Form not correctly completed. Please see item(s) in red text below.'));
        	}

            // Check client users
            if (!empty($clientId))
            {
                // Get client colleagues
                $clientUserMapper          = new Application_Model_ClientsUserMapper();
                $this->view->clientUsers   = $clientUserMapper->fetchByClientId($clientId);
                $this->view->primaryUserId = $formData['primary_user_id'];

                if ($isShareAccess)
                {
                    $this->view->selectedClientUsers = $formData['shareaccess'];
                }
            }
        }

        // create js warning script for file quality
        // Note: we do this here because we need to check from the db which audio quality elements need to have a warning applied to them
        $audioFileQualityMapper = new Application_Model_AudioFileQualityMapper();
        $audioFileQualities = $audioFileQualityMapper->fetchAllForDropdown();

        $warningJs = '';
        foreach($audioFileQualities as $quality)
        {
        	if (!empty($quality['warning']))
            {
        		$warningJs .= 'if ($("#audio_quality_id").val() == ' . $quality['key'] . ') {alert(\'' . $quality['warning'] . '\')}';
        	}
        }
        $this->view->audioFileQualitiesWarningJs = 'function checkAudioQualityWarnings(){' . $warningJs . '}';

		$this->view->form = $form;
		// if a client is creating job then refresh the primary contact dropdown on load
		if ($this->_identity->acl_group_id == 3)
        {
			$this->view->refreshPrimaryContactIdOnLoad = true;
		}
        else
        {
			$this->view->refreshPrimaryContactIdOnLoad = false;
		}

		$this->view->isAdmin = ($this->_identity->acl_role_id == 1) ? true : false;
	}

    /**
     * Fetch relevant services for selected client
     *
     * @return void
     */
    public function fetchClientServicesAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $clientId = $this->getRequest()->getParam( 'client_id', null );

        $output = array(
            'services' => '-'
        );

        if ( $clientId > 0 )
        {
            $clientMapper           = new Application_Model_ClientMapper();
            $client                 = $clientMapper->fetchRow( $clientMapper->select()->where( 'id = ?', $clientId ) );
            $services               = $client->getAllServices();
            $output['services']     = $this->view->partial( 'job/_partials/_services.phtml', 'default', array( 'services' => $services, 'clientId' => $clientId ) );
        }

        echo json_encode( $output );
    }

    /**
     * Fetch relevant details for selected service
     *
     * @return void
     */
    public function fetchClientServicesDetailsAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $clientId  = $this->getRequest()->getParam( 'client_id', null );
        $serviceId = $this->getRequest()->getParam( 'service_id', null );

        $output = array(
            'turnaround_times' => '-',
            'speaker_numbers'  => '-'
        );

        if ( $clientId > 0 && $serviceId > 0 )
        {
            $serviceMapper              = new Application_Model_Service();
            $service                    = $serviceMapper->fetchRow( $serviceMapper->select()->where( 'id = ?', $serviceId ) );
            $output['turnaround_times'] = $this->view->partial( 'job/_partials/_turnaroundTimes.phtml', 'default', array( 'turnaroundTimes' => $service->getServiceTurnaroundTimes() ) );
            $output['speaker_numbers']  = $this->view->partial( 'job/_partials/_speakerNumbers.phtml', 'default', array( 'speakerNumbers' => $service->getServiceSpeakerNumbers() ) );
            $output['modifiers']        = $this->view->partial( 'job/_partials/_priceModifiers.phtml', 'default', array( 'service' => $service ) );
        }

        echo json_encode( $output );
    }

    public function viewAction()
    {
        // action body
    	$id         = $this->_request->getParam('id');

    	$jobMapper              = new Application_Model_JobMapper();
    	$data                   = $jobMapper->fetchById($id);

        $data['price-discount'] = $jobMapper->getJobPriceWithDiscount($data);

        $data['price']          = $jobMapper->getJobPrice($data);

        $data['discount']       = $jobMapper->getJobDiscountPercentage($data);

    	if (!$this->_acl->isAccessAllowed($this->_identity->acl_role_id, $data['acl_resource_id'], 10))
        {
    		$this->_acl->denyAccess();
    	}

    	$this->view->job = $data;

    	$audioJobMapper = new Application_Model_AudioJobMapper();

    	switch ($this->_identity->acl_group_id)
        {
            case App_Controller_Plugin_Acl::ACL_GROUP_ADMIN:
            case App_Controller_Plugin_Acl::ACL_GROUP_SUPERADMIN:
    			// set the auio job phtml file we'll use inside the view
    			$this->view->audioJobPhtml = 'list-for-job';
    			// get the audio jobs data

                if ( '1' == $data['archived'] )
                {
                    $audioJobs = $audioJobMapper->fetchAllArchiveOption( $id );
                }
                else
                {
                    $audioJobs = $audioJobMapper->fetchNonArchived( $id );
                }

    			$jobStatuses = $audioJobMapper->fetchAllStatusesForDropdown();
    			$this->view->audioJobStatuses = $jobStatuses;

    			$prioritiesMapper = new Application_Model_PriorityMapper();
    			$jobPriorities = $prioritiesMapper->fetchAllForDropdown();
    			$this->view->audioJobPriorities = $jobPriorities;
    			break;
    		case 3: // client
    			// set the audio job phtml file we'll use inside the view
    			$this->view->audioJobPhtml = 'list-for-job-client';

                $adHocMapper              = new Application_Model_AdHocMapper;
                $this->view->adHocCharges = $adHocMapper->getServicesByJob($id);

    			// get the audio jobs data
    			$audioJobs = $audioJobMapper->fetchForClientByJobId($id);
    			break;
    		default:
    			exit();
    	}
        // Set linked audio file durations in the main file only
        //$audioJobs = $this->view->setLinkedAudioFileLength($audioJobs);

        $audioJobs = $audioJobMapper->populatePrices( $audioJobs, true );

    	// set edit permissions on audio jobs
    	$audioJobCanEdit = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'audio-job','edit');
    	$this->view->audioJobCanEdit = $audioJobCanEdit;

    	$audioJobCanUpload = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'audio-job','upload');

        if (count($audioJobs) > 0)
        {
            // add canEdit info to $audioJobs so can pass into partialLoop which creates the audio jobs related to this job
            array_walk($audioJobs, array($this, 'addCanEdit'), $audioJobCanEdit);
            array_walk($audioJobs, array($this, 'addCanUpload'), $audioJobCanUpload);
            array_walk($audioJobs, array($this, 'addIsLead'));
            array_walk( $audioJobs, array( $this, 'hasSubStandardLiabilities' ) );
        }

    	$this->view->audioJobs = $audioJobs;

    	$audioJobCanArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'audio-job','archive');
    	$this->view->audioJobCanArchive = $audioJobCanArchive;

    	$supportFileMapper = new Application_Model_SupportFileMapper();
    	$fileData = $supportFileMapper->fetchCurrentByJobId($id);

    	// archive permissions
    	$supportFileCanArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'support-file','archive');
    	array_walk($fileData, array($this, 'supportFileCanArchive'), $supportFileCanArchive);
    	$this->view->supportFiles = $fileData;

    	$tabId = $this->_request->getParam('tab_id', 0);
    	$this->view->tabId = $tabId;

    	// set permissions for actions on this view
    	$this->view->canEdit = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','edit');
    	$this->view->canUpload = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'job','upload');

        if ( 1 == $data['status_complete'] && $this->_acl->isClient() )
        {
            $this->view->canUpload = false;
        }

    	$this->view->customClass = '-project';
        $this->view->isAdmin     = $this->_acl->isAdmin();
    }

    /**
     * Service info action
     *
     * @return void
     */
    public function serviceInfoAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $clientId = $this->getRequest()->getParam( 'client_id', null );

        if( null !== $clientId )
        {
            $clientMapper = new Application_Model_ClientMapper();
            $client       = $clientMapper->fetchRow( 'id = ' . $clientId );
            $services     = $client->getAllServices();
        }
        else
        {
            $serviceMapper = new Application_Model_Service();
            $services      = $serviceMapper->fetchAll();
        }

        $additionalServices = array();
        foreach ( $services as $serviceRow )
        {
            foreach ( $serviceRow->getServicePriceModifiers() as $serviceModifier )
            {
                $additionalServices[ $serviceModifier->getPriceModifier()->id ] = $serviceModifier->getPriceModifier();
            }
        }
        $this->view->services           = $services;
        $this->view->additionalServices = $additionalServices;

        $output = array(
            'html' => $this->view->partial(
                'job/_partials/_serviceDescriptions.phtml',
                array(
                    'services'           => $services,
                    'additionalServices' => $additionalServices
                )
            )
        );

        echo json_encode( $output );
    }

    public function downloadAction()
    {
    	// action body
    	$id = $this->_request->getParam('id');
    	$jobMapper = new Application_Model_JobMapper();
    	$job = new Application_Model_Job();
    	$jobMapper->find($id, $job);

    	header('Content-Type: ' . $job->getFileMimeType());
    	header('Content-Disposition: attachment; filename="' . $job->getFileName() . '"');
    	readfile(APPLICATION_PATH.'/../data/' . $job->getId());

    	// disable layout and view
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    }

    public function editAction()
    {
        // action body
    	$request = $this->getRequest();
    	$id = $this->_request->getParam('id');

    	$form = new Application_Form_JobEdit();

    	$jobMapper = new Application_Model_JobMapper();

        $isShareAccess = false;

    	if ($this->getRequest()->isPost())
        {
            $formData = $this->getRequest()->getPost();

            // Add users granted access to the additional recipients
            if (isset($formData['shareaccess']))
            {
                $isShareAccess = true;
            }

            $additionalTranscriptRecipients = $formData['additional_transcript_recipients'];

    		if ($form->isValid($request->getPost()))
            {
    			$data = $formData;
    			// remove potentially null values
        		if (isset($data['speaker_numbers_id']) && $data['speaker_numbers_id'] == 0) {
        			unset($data['speaker_numbers_id']);
        		}

        		if (isset($data['audio_quality_id']) && $data['audio_quality_id'] == 0) {
         			unset($data['audio_quality_id']);
        		}

        		if (isset($data['turnaround_time_id']) && $data['turnaround_time_id'] == 0) {
         			unset($data['turnaround_time_id']);
        		}

        		if (isset($data['transcription_type_id']) && $data['transcription_type_id'] == 0) {
         			unset($data['transcription_type_id']);
        		}

                if ( isset( $data['price_modifiers'] ) )
                {
                    $data['modifiers'] = serialize( $data['price_modifiers'] );
                    unset( $data['price_modifiers'] );
                }

                $data['additional_transcript_recipients'] = $additionalTranscriptRecipients;

                if ( isset( $data['shareaccess'] ) )
                {
                    unset( $data['shareaccess'] );
                }
    			$jobMapper  = new Application_Model_JobMapper();

    			if ($jobMapper->save($data)) {

    				// email notification
    				// make email action from status name
    				$data = $jobMapper->fetchById($id);
    				$status = $jobMapper->lookupStatusById($data['status_id']);

                    // Grant/remove access to primary user colleagues
                    $shareAccessList = array();
                    if ($isShareAccess)
                    {
                        $shareAccessList = $formData['shareaccess'];
                    }

                    // If primary user has been changed we need to ensure the new primary user will get access to the project + audio jobs
                    if (!$this->view->userHasJobAccess($data['primary_user_id'], $id))
                    {
                        $shareAccessList[] = $data['primary_user_id'];
                    }

                    $this->_shareJobAccess($id, $formData['client_id'], $data['primary_user_id'], $shareAccessList);

    				if ($status == 'Completed') {
	    				$camel = ucwords($status);
	    				$action = trim('job' . $camel);

	    				$options = array(
							'emailType' => $action,
	    				    'id'		=> $data['id']
	    				);

	    				$this->_email->send($this->view, $options);
    				}

    				$this->_helper->FlashMessenger('Record Saved!');
    				return $this->_helper->redirector->gotoSimple('edit', 'job', 'default', array('id' => $id));
    			} else {
    				$this->_helper->FlashMessenger(array('error' => 'Record Not Saved!'));
    			}
    		}
            else
            {
                if ( $data['client_id'] > 0 )
                {
                    $clientMapper         = new Application_Model_ClientMapper();
                    $client               = $clientMapper->fetchRow( 'id = ' . $data['client_id'] );
                    $this->view->services = $client->getAllServices();

                    if ( $data['service_id'] )
                    {
                        $serviceMapper = new Application_Model_Service();
                        $service       = $serviceMapper->fetchRow( 'id = ' . $data['service_id'] );
                        $this->view->service = $service;
                    }
                    if ( $data['turnaround_time_id'] > 0 )
                    {
                        $this->view->turnaround_time_id = $data['turnaround_time_id'];
                    }

                    if ( $data['speaker_numbers_id'] > 0 )
                    {
                        $this->view->speaker_numbers_id = $data['speaker_numbers_id'];
                    }
                }
    			$this->_helper->FlashMessenger(array('error' => 'Form not correctly completed. Please see item(s) in red text below.'));
    		}

    		$data = $jobMapper->fetchById($id);
    	} else {
    		$data = $jobMapper->fetchById($id);

            if ( $data['client_id'] > 0 )
            {
                $clientMapper         = new Application_Model_ClientMapper();
                $client               = $clientMapper->fetchRow( 'id = ' . $data['client_id'] );
                $this->view->services = $client->getAllServices();

                if ( $data['service_id'] )
                {
                    $serviceMapper = new Application_Model_Service();
                    $service       = $serviceMapper->fetchRow( 'id = ' . $data['service_id'] );
                    $this->view->service = $service;
                }
                if ( $data['turnaround_time_id'] > 0 )
                {
                    $this->view->turnaround_time_id = $data['turnaround_time_id'];
                }

                if ( $data['speaker_numbers_id'] > 0 )
                {
                    $this->view->speaker_numbers_id = $data['speaker_numbers_id'];
                }
            }

    		$form->populate($data);
    		// set defaults for select elements
    		$form->setDefault('status_id', $data['status_id']);
    		$form->setDefault('job_due_date', $data['job_due_date']);
    		$form->setDefault('client_id', $data['client_id']);
    		$form->setDefault('priority_id', $data['priority_id']);

            if ( !empty( $data['service_id'] ) )
            {
                $serviceMapper       = new Application_Model_Service();
                $service             = $serviceMapper->fetchRow( 'id = ' . $data['service_id'] );
                $this->view->service = $service;
            }

            $clientMapper         = new Application_Model_ClientMapper();
            $client               = $clientMapper->fetchRow( 'id = ' . $data['client_id'] );
            $services             = $client->getAllServices();
            $this->view->services = $services;

            // Check client users
            $clientId = $data['client_id'];
            if (!empty($clientId))
            {
                // Get client colleagues
                $clientUserMapper          = new Application_Model_ClientsUserMapper();
                $this->view->clientUsers   = $clientUserMapper->fetchByClientId($clientId);
                $this->view->primaryUserId = $data['primary_user_id'];
                $this->view->isEditAction  = true;
                $this->view->jobId         = $id;

                $this->view->clientId = $clientId;

                if (isset($formData['shareaccess']))
                {
                    $this->view->selectedClientUsers = $formData['shareaccess'];
                }
            }
    	}

    	// create js warning script for file quality
    	// Note: we do this here because we need to check from the db which audio quality elements need to have a warning applied to them
    	$audioFileQualityMapper = new Application_Model_AudioFileQualityMapper();
    	$audioFileQualities = $audioFileQualityMapper->fetchAllForDropdown();

    	$warningJs = '';
    	foreach($audioFileQualities as $quality) {
    		if (!empty($quality['warning'])) {
    			$warningJs .= 'if ($("#audio_quality_id").val() == ' . $quality['key'] . ') {alert(\'' . $quality['warning'] . '\')}';
    		}
    	}
    	$this->view->audioFileQualitiesWarningJs = 'function checkAudioQualityWarnings(){' . $warningJs . '}';

    	$clientsUserMapper = new Application_Model_ClientsUserMapper();
    	$primaryUserIds = $clientsUserMapper->fetchByClientIdForDropdown($data['client_id']);
    	$element = $form->getElement('primary_user_id');
    	$element->addMultiOptions($primaryUserIds);


    	$this->view->job = $data;
    	$this->view->form = $form;
    	$this->view->isAdmin = ($this->_identity->acl_role_id == 1) ? true : false;

    }

    public function upload()
    {
    	$adapter = new Zend_File_Transfer_Adapter_Http();
    	$jobId = $this->_request->getParam('job_id');

    	// get job due date to assign to audio job
    	$jobMapper  = new Application_Model_JobMapper();
    	$job = $jobMapper->fetchById($jobId);
    	$clientDueDate = $job['job_due_date'];

    	$adapter->setDestination(APPLICATION_PATH.$this->uploads);
//     		$adapter->addValidator('Extension', false, 'jpg,png,gif');

    	$files = $adapter->getFileInfo();
    	foreach ($files as $file => $info) {
    		$tmp = time() . rand();
    		$adapter->addFilter('Rename', APPLICATION_PATH . $this->uploads . $tmp);

    		$originalFileName = $adapter->getFileName($file, false);
    		$originalFileExtension = substr($originalFileName, strpos($originalFileName, '.')+1);
    		$originalFileMimeType = $adapter->getMimeType($file);
    		$originalFileSize = $adapter->getFileSize($file);

    		// file uploaded & is valid
    		if (!$adapter->isUploaded($file)) continue;

			try {
    			// This takes care of the moving and making sure the file is there
    			$adapter->receive($file);
    		} catch (Zend_File_Transfer_Exception $e) {
    			echo $e->message();
    		}

    		$docType = $this->_request->getParam('doc_type');

    		// set return data object for frontend
    		$fileclass = new stdClass();

    		if ($docType == 'audio') {

    			$audioLength = $this->getFileLengthAction(APPLICATION_PATH . '/../data/tmp/' . $tmp);


//     			Zend_Debug::dump($audioLength);

    			$fileclass->length = $audioLength;

	    		$mapper  = new Application_Model_AudioJobMapper();
				$data = array();
				$data['job_id'] = $jobId;

				$speakerNumbers = $this->_request->getParam('speakers');
				$data['speaker_numbers_id'] = $speakerNumbers[0];

                $leadId = $this->_request->getParam('lead_id');
				$data['lead_id'] = $leadId[0];

				$audioQuality = $this->_request->getParam('audio_quality');
				$data['audio_quality_id'] = $audioQuality[0];

				$turnaround = $this->_request->getParam('turnaround');
				$data['turnaround_time_id'] = $turnaround[0];

				$service = $this->_request->getParam('service');
				$data['service_id'] = $service[0];

                /**
                 * The upload form won't allow us to get the services for each job any other way
                 */
                $serviceMapper  = new Application_Model_Service();
                $service        = $serviceMapper->fetchRow( 'id = ' . $service[0] );
                $modifiers      = $service->getServicePriceModifiers();
                $priceModifiers = array();

                foreach ( $modifiers as $modifier )
                {
                    $rawModifier = $this->_request->getParam( 'price_modifiers_' . $modifier->id, null );
                    if ( null !== $rawModifier[0] )
                    {
                        $priceModifiers[] = $rawModifier[0];
                    }
                }
                $data['price_modifiers'] = $priceModifiers;

				$comments = $this->_request->getParam('client_comments');
				$data['client_comments'] = $comments[0];

				$data['status_id'] = 1;

				$data['length_seconds'] = $audioLength;

				$uploadFileCount = $this->_request->getParam('audio_upload_file_count');
// 				if (!is_null($audioLength)) {
					$data['upload_file_count'] = $uploadFileCount;
// 				} else{
// 					$data['upload_file_count'] = null;
// 				}

				$uploadKey = $this->_request->getParam('audio_upload_key');
// 				if (!is_null($audioLength)) {
					$data['upload_key'] = $uploadKey;
// 				} else {
// 					$data['upload_key'] = null;
// 				}
	    		$data = array_merge($data, array('file_name' => $originalFileName, 'size' => $originalFileSize,'mime_type' => $originalFileMimeType));

	    		$id = $mapper->save($data);

                $this->_shareAudioJobAccess($data['job_id'], $id);

	    		// add acl access rights to this audio job
	    		$audioJob = $mapper->fetchAclResourceIdById($id);
	    		$aclResourceId = $audioJob['acl_resource_id'];

	    		$this->_insertAcl($aclResourceId, $this->_identity->acl_role_id, 18, 'allow');  // audio-job/list

	    		// move tmp file to storage location and rename to job_id
	    		rename(APPLICATION_PATH.'/../data/tmp/' . $tmp, APPLICATION_PATH.'/../data/' . $id);

	    		if (!is_null($audioLength)) {
	    			// check if should send email notification
	    			$uploadKeyCount = $mapper->getUploadKeyCountForFilesWithLengthIsNotNull($uploadKey);
	    		}


	    		if (is_null($audioLength)) {
	    			$fileclass->no_file_length = true;
	    			$fileclass->file_id = $id;
	    			$fileclass->upload_key = $uploadKey;
	    			$fileclass->upload_file_count = $uploadFileCount;
	    		}

	    		if (!is_null($audioLength)) {
			    	if ($uploadKeyCount == $uploadFileCount) {

			    		// update the records with $uploadKey
			    		$mapper->setEmailNotificationByUploadKey($uploadKey);

				    	// send email notification
				    	$options = array(
				    		'emailType' => 'audioJobReceived',
				    		'uploadKey' => $uploadKey,
                            'job_id'    => $jobId
				    	);
						$this->_email->send($this->view, $options);
			    	}
	    		}


    		} elseif ($docType == 'support') {

    			// set filelength to -1 to indicate not value
    			$fileclass->length = -1;

    			$data = array();
    			$data['job_id'] = $jobId;

    			$supportUploadFileCount = $this->_request->getParam('support_upload_file_count');
    			$data['upload_file_count'] = $supportUploadFileCount;

    			$supportUploadKey = $this->_request->getParam('support_upload_key');
    			$data['upload_key'] = $supportUploadKey;

    			$data = array_merge($data, array('file_name' => $originalFileName, 'size' => $originalFileSize,'mime_type' => $originalFileMimeType));

    			$mapper  = new Application_Model_SupportFileMapper();
    			$id = $mapper->save($data);

    			// move tmp file to storage location and rename to job_id
    			rename(APPLICATION_PATH.'/../data/tmp/' . $tmp, APPLICATION_PATH.'/../data/support/' . $id);


    			// check if should send email notification
				$supportUploadKeyCount = $mapper->getUploadKeyCount($supportUploadKey);

	    		if ($supportUploadKeyCount == $supportUploadFileCount) {
	    			// send email notification
	    			$options = array(
	    				'emailType' => 'supportFileReceived',
	    				'uploadKey' => $supportUploadKey,
                        'job_id'    => $jobId
	    			);

	    			$this->_email->send($this->view, $options);
		    	}
    		}


    		// set return data for frontend
    		$fileclass->name = str_replace(APPLICATION_PATH.$this->uploads, 'Upload Complete:   ', preg_replace('/\d\//','',$originalFileName));
    		$fileclass->size = $originalFileSize;
    		$fileclass->type = $originalFileMimeType;
			$fileclass->url = '/';

    		$datas[] = $fileclass;
    	}

    	header('Pragma: no-cache');
    	header('Cache-Control: private, no-cache');
    	header('Content-Disposition: inline; filename="files.json"');
    	header('X-Content-Type-Options: nosniff');
    	header('Vary: Accept');
    	echo json_encode($datas);
    }

    private function _reupload()
    {
    	// set return data object for frontend
    	$fileclass = new stdClass();

    	$audioJobId = $this->_request->getParam('id');
    	$jobId = $this->_request->getParam('job_id');

    	$mapper  = new Application_Model_AudioJobMapper();
    	$data = array();
    	$data['id'] = $audioJobId;
    	$uploadKey = $this->_request->getParam('audio_upload_key');
    	$data['upload_key'] = $uploadKey;

    	$audioLength = ($this->_request->getParam('hours') * 60 * 60) + ($this->_request->getParam('minutes') * 60);
    	$data['length_seconds'] = $audioLength;

    	$uploadKey = $this->_request->getParam('audio_upload_key');
    	$id = $mapper->save($data);

    	$uploadFileCount = $this->_request->getParam('upload_file_count');

    	// check if should send email notification
    	$uploadKeyCount = $mapper->getUploadKeyCountForFilesWithLengthIsNotNull($uploadKey);

    	if ($uploadKeyCount == $uploadFileCount) {

    		// update the records with $uploadKey
			$mapper->setEmailNotificationByUploadKey($uploadKey);

    		// send email notification
    		$options = array(
    			'emailType' => 'audioJobReceived',
    			'uploadKey' => $uploadKey,
                'job_id'    => $jobId
    		);
    		$this->_email->send($this->view, $options);
    	}

    	// set return data for frontend
    	$fileclass->name = 'Thank you! Sound file duration received';
    	$fileclass->status = 'ok';
    	$datas = $fileclass;

    	header('Pragma: no-cache');
    	header('Cache-Control: private, no-cache');
    	header('Content-Disposition: inline; filename="files.json"');
    	header('X-Content-Type-Options: nosniff');
    	header('Vary: Accept');
    	echo json_encode($datas);
    }

    public function getFileLengthAction($file)
    {
    	// include getID3() library (can be in a different directory if full path is specified)
    	require_once('GetId3/getid3/getid3.php');

    	// Initialize getID3 engine
    	$getID3 = new getID3;

     	// Analyze file and store returned data in $ThisFileInfo
    	$ThisFileInfo = $getID3->analyze($file);

        if (isset($ThisFileInfo['playtime_seconds']))
        {
            return $ThisFileInfo['playtime_seconds'];
        }

        return null;
    }

    protected function _insertAcl($aclResourceId, $aclRoleId, $aclPrivilegeId, $mode='deny')
    {
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

    /**
     * Grant users access to job
     *
     * @param int $jobId
     * @param int $clientId
     * @param int $primaryUserId
     * @param array $shareAccessUserList
     *
     * @return void
     */
    protected function _shareJobAccess($jobId, $clientId, $primaryUserId, $shareAccessUserList)
    {
        if (!empty($shareAccessUserList))
        {
            $aclMapper      = new Application_Model_AclMapper();
            $jobMapper      = new Application_Model_JobMapper();
            $audioJobMapper = new Application_Model_AudioJobMapper();
            $userMapper     = new Application_Model_UserMapper();

            // Job details
            $job              = $jobMapper->fetchById($jobId);
            $jobAclResourceId = $job['acl_resource_id'];

            // Remove primary user from the add list
            foreach ($shareAccessUserList as $userId)
            {
                $user          = $userMapper->fetchById($userId);
                $userAclRoleId = $user['acl_role_id'];

                // Check if this user already has access to this audio file
                if (!$aclMapper->hasResourceAccess($userAclRoleId, $jobAclResourceId, 10))
                {
                    // Share job access
                    $aclMapper->shareJobAccess($userAclRoleId, $jobAclResourceId);

                    // Share audio jobs
                    $audioJobs = $audioJobMapper->fetchByJobId($jobId);
                    if (!empty($audioJobs))
                    {
                        foreach ($audioJobs as $audioJob)
                        {
                            $aclMapper->shareAudioJobAccess($userAclRoleId, $audioJob['acl_resource_id']);
                        }
                    }

                    // Send share access notification to shared access colleages
                    // Exclude the primary user as this job is not shared with them, they are the MAIN user
                    if ($userId !== $primaryUserId)
                    {
                        $shareAccessEmail = new App_Mail_ShareAccessEmail();
                        $shareAccessEmail->setView($this->view);
                        $shareAccessEmail->setJob($jobId);
                        $shareAccessEmail->setReceiver($user['email']);
                        $shareAccessEmail->setReceiverName($user['name']);
                        $shareAccessEmail->sendMail();
                    }
                }
            }
        }

        // Remove users who have no more access to this job
        $this->_removeJobAccess($jobId, $clientId, $primaryUserId, $shareAccessUserList);
    }

    /**
     * Assign newly added audio jobs to all users who have shared access on a job
     *
     * @param int $jobId
     * @param int $audioJobId
     *
     * @return void
     */
    protected function _shareAudioJobAccess($jobId, $audioJobId)
    {
        $clientUserMapper = new Application_Model_ClientsUserMapper();
        $jobMapper        = new Application_Model_JobMapper();
        $audioJobMapper   = new Application_Model_AudioJobMapper();

        // Fetch job and audio job
        $job      = $jobMapper->fetchById($jobId);
        $audioJob = $audioJobMapper->fetchById($audioJobId);

        // Fetch all colleagues of the primary user
        $clientColleagues = $clientUserMapper->fetchClientUserColleagues($job['primary_user_id'], $job['client_id']);
        if (!empty($clientColleagues))
        {
            $aclMapper = new Application_Model_AclMapper();
            foreach($clientColleagues as $user)
            {
                // If the user has access to the job add access for the audio job
                if ($this->view->userHasJobAccess($user['user_id'], $jobId))
                {
                    // Share audio jobs
                    $aclMapper->shareAudioJobAccess($user['acl_role_id'], $audioJob['acl_resource_id']);
                }
            }
        }
    }

    /**
     * Remove users access from job who is not primary user or has been given shared access.
     * Also removes previous client and users access if the client on the project was changed
     *
     * @param int $primaryUserId
     * @param int $clientId
     * @param array $shareAccessUserList
     *
     * @return void
     */
    protected function _removeJobAccess($jobId, $clientId, $primaryUserId, $shareAccessUserList)
    {
        $aclMapper        = new Application_Model_AclMapper();
        $clientUserMapper = new Application_Model_ClientsUserMapper();
        $jobMapper        = new Application_Model_JobMapper();
        $audioJobMapper   = new Application_Model_AudioJobMapper();

        // Fetch job details
        $job = $jobMapper->fetchById($jobId);

        // Remove users with no longer access to job
        $checkUsersAccessList = $clientUserMapper->fetchClientUserColleagues($primaryUserId, $clientId);

        // When client change is trigger we need to remove access from previous client and its users
        $otherClientUsersWithAccess = $aclMapper->fetchOtherClientUsersWithAccess($job['acl_resource_id'], $job['client_id']);
        if (!empty($otherClientUsersWithAccess))
        {
            $checkUsersAccessList = array_merge($checkUsersAccessList, $otherClientUsersWithAccess);
        }

        if (!empty($checkUsersAccessList))
        {
            $jobAclResourceId = $job['acl_resource_id'];
            foreach ($checkUsersAccessList as $user)
            {
                $userAclRoleId = $user['acl_role_id'];
                $userId        = $user['user_id'];
                if (!in_array($userId, $shareAccessUserList))
                {
                    // Remove access from project
                    $aclMapper->removeJobAccess($userAclRoleId, $jobAclResourceId);

                    // Remove audio jobs access
                    $audioJobs = $audioJobMapper->fetchByJobId($jobId);
                    if (!empty($audioJobs))
                    {
                        foreach ($audioJobs as $audioJob)
                        {
                            $aclMapper->removeAudioJobAccess($userAclRoleId, $audioJob['acl_resource_id']);
                        }
                    }
                }
            }
        }
    }


}