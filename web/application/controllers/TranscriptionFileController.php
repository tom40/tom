<?php

class TranscriptionFileController extends App_Controller_Action
{
	private $uploads = '/../data/tmp_transcription/';

    public function init()
    {
        /* Initialize action controller here */
    	$this->_email = $this->_helper->getHelper('email');
        parent::init();
    }

    public function indexAction()
    {
    }

    public function archiveAction()
    {
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$id = $this->_request->getParam('id');

    	$transcriptionCanArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'transcription-file','archive');
    	if ($transcriptionCanArchive) {

	    	if (!empty($id)) {
	    		$mapper  = new Application_Model_TranscriptionFileMapper();
	    		$data = array();
	    		$data['id'] = $id;
	    		$data['archived'] = 1;
	    		$id = $mapper->save($data);

	    		$output['status'] = 'ok';
	    	} else {
	    		$output['status'] = 'fail';
	    	}
    	} else {
    		$output['status'] = 'fail';
    	}
    	echo json_encode($output);
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

    	if ($this->_request->isGet()) {
    		$this->view->hasTopMenu = false;
    		$id = $this->_request->getParam('id');

            // Check if current user is a proofreader for this audio job
            if ( Zend_Auth::getInstance()->hasIdentity() ) {
                $prModel       = new Application_Model_AudioJobProofreaderMapper();
                $isProofreader = $prModel->isAudioJobProofreader(Zend_Auth::getInstance()->getIdentity()->id, $id);

                // Set in view for proofread checkbox
                $this->view->isAudioJobProofreader = $isProofreader;
            }

    		$audioJobMapper = new Application_Model_AudioJobMapper();
    		$data = $audioJobMapper->fetchById($id);
    		$this->view->audioJob = $data;
    	}
    }

    public function downloadAction()
    {
    	// disable layout and view
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	// action body
    	$id = $this->_request->getParam('id');

    	// record the download
    	$transcriptionFileDownloadMapper = new Application_Model_TranscriptionFileDownloadMapper();
    	$data = array();
    	$data['transcription_file_id'] = $id;
    	$transcriptionFileDownloadMapper->save($data);

    	$transcriptionFileMapper = new Application_Model_TranscriptionFileMapper();
    	$transcriptionFile = $transcriptionFileMapper->fetchById($id);

    	header('Content-Type: ' . $transcriptionFile['mime_type']);
    	header('Content-Disposition: attachment; filename="' . $transcriptionFile['file_name'] . '"');
        header("Pragma: public");
        header("Cache-Control: public");
    	readfile(APPLICATION_PATH.'/../data/transcription/' . $transcriptionFile['id']);
    }


    public function upload()
    {

		$request = $this->getRequest();
    	$adapter = new Zend_File_Transfer_Adapter_Http();

    	$adapter->setDestination(APPLICATION_PATH.$this->uploads);

    	$files = $adapter->getFileInfo();
    	foreach ($files as $file => $info) {
    		$tmp = rand();
    		$adapter->addFilter('Rename', APPLICATION_PATH . '/../data/tmp_transcription/' . $tmp);

    		$originalFileName = $adapter->getFileName($file, false);
    		$originalFileExtension = substr($originalFileName, strpos($originalFileName, '.')+1);
    		$originalFileMimeType = $adapter->getMimeType($file);
    		$originalFileSize = $adapter->getFileSize($file);

    			// file uploaded & is valid
    		if (!$adapter->isUploaded($file)) continue;
//     			if (!$adapter->isValid($file)) continue;

    			// receive the files into the user directory
//     			$adapter->receive($file); // this has to be on top

    			// you could apply a filter like this too (if you want), to rename the file:
    			//  $filterFileRename = new Zend_Filter_File_Rename(array('target' => $rename));
    			//  $filterFileRename->filter($name); // this has to use name


			try {
    			//This takes care of the moving and making sure the file is there
    			$adapter->receive($file);
			} catch (Zend_File_Transfer_Exception $e) {
    			echo $e->message();
    		}

			$data = array();
			$data['audio_job_id'] = $this->_request->getParam('audio_job_id');
			$hoursTaken = $this->_request->getParam('hours');
			$minutesTaken = $this->_request->getParam('minutes');
			$data['minutes_taken'] = ($hoursTaken * 60) + $minutesTaken;
			$comment = $this->_request->getParam('comment');
			$data['comment'] = $comment[0];
			$proofread = $this->_request->getParam('proofread');
			if ($proofread[0] == 'on') {
				$data['proofread'] = 1;
			} else {
				$data['proofread'] = 0;
			}

    		$data = array_merge($data, array('file_name' => $originalFileName, 'size' => $originalFileSize,'mime_type' => $originalFileMimeType));

    		$mapper  = new Application_Model_TranscriptionFileMapper();
    		$id = $mapper->save($data);


            rename(APPLICATION_PATH.'/../data/tmp_transcription/' . $tmp, APPLICATION_PATH.'/../data/transcription/' . $id);

    		// email notification
    		if ($data['proofread'] == 1) {
				$action = trim('audioJobProofreaderTranscriptUploaded');
    		} else {
    			$action = trim('audioJobTypistTranscriptUploaded');
    		}
    		$options = array(
				'emailType' => $action,
    			'id'		=> $id
    		);
	    	$this->_email->send($this->view, $options);

    		$fileclass = new stdClass();

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


     public function setupDownloadAction()
     {
     	// disable layout and view
     	$this->view->layout()->disableLayout();
     	$this->_helper->viewRenderer->setNoRender(true);

     	// action body
     	$id = $this->_request->getParam('id');

     	 //TODO: this section but for proofreaders ??
     	// record the download for this user
     	$audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
     	$audioTypist          = $audioJobTypistMapper->fetchRowByAudioJobIdCurrentUserIdTypistIsCurrent($id);

     	 //if we have record then the current user is one of the assigned typists so need to update audio_jobs_typists to show they have downloaded the record
     	if ($audioTypist) {
     		$data = array();
     		$data['id'] = $audioTypist['id'];
     		$data['downloaded'] = 1;
     		$audioJobTypistMapper->save($data);

     		$audioJobMapper = new Application_Model_AudioJobMapper();
     		$audioJob = $audioJobMapper->fetchById($id);

     		$data = array();

     		// add in the audio_jobis_typist_id for the record being accepted
     		$audioJob['audio_jobs_typists_id'] = $audioTypist['id'];
     		$this->view->data = $audioJob;

     		$this->_helper->viewRenderer->setNoRender(true);
     		$outputHtml = $this->view->render('audio-job/typist-list-cell.phtml');
     	} else {
     		 //just return blank html
     		$outputHtml = '';
     	}

     	$outputStatus = 'ok';
     	$outputHtml = '';

     	$output['status'] = $outputStatus;
     	$output['html']   = $outputHtml;

     	echo json_encode($output);
     }


}