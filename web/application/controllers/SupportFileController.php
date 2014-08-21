<?php

class SupportFileController extends App_Controller_Action
{
	private $uploads = '/../data/tmp_support/';

    public function init()
    {
        /* Initialize action controller here */
    	$this->_email = $this->_helper->getHelper('email');
        parent::init();
    }

    public function indexAction()
    {
    }

    public function listAction()
    {
    	$jobId = $this->_request->getParam('job_id');

    	$supportFileMapper = new Application_Model_SupportFileMapper();
    	$data = $supportFileMapper->fetchCurrentByJobId($jobId);

    	// archive permissions
    	$supportFileCanArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'support-file','archive');
    	array_walk($data, array($this, '_supportFileCanArchive'), $supportFileCanArchive);
    	$this->view->supportFiles = $data;

    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	$outputHtml = $this->view->render('support-file/list-for-job.phtml');

    	$output['html'] = $outputHtml;
    	$output['status'] = 'ok';

    	echo json_encode($output);

    }

    private function _supportFileCanArchive(&$itemArray, $key, $supportFileCanArchive)
    {
    	$itemArray['supportFileCanArchive'] = $supportFileCanArchive;
    }

    public function archiveAction()
    {
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$id = $this->_request->getParam('id');

    	$supportFileCanArchive = $this->_acl->isAccessAllowed(Zend_Auth::getInstance()->getIdentity()->id,'support-file','archive');
    	if ($supportFileCanArchive) {
	    	if (!empty($id)) {
	    		$mapper  = new Application_Model_SupportFileMapper();
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
    		$jobId = $this->_request->getParam('job_id');
    		$JobMapper = new Application_Model_JobMapper();
    		$data = $JobMapper->fetchById($jobId);
    		$this->view->job = $data;
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
    	$supportFileDownloadMapper = new Application_Model_SupportFileDownloadMapper();
    	$data = array();
    	$data['support_file_id'] = $id;
    	$supportFileDownloadMapper->save($data);

    	$supportFileMapper = new Application_Model_SupportFileMapper();
    	$supportFile = $supportFileMapper->fetchById($id);

    	header('Content-Type: ' . $supportFile['mime_type']);
    	header('Content-Disposition: attachment; filename="' . $supportFile['file_name'] . '"');
        header("Pragma: public");
        header("Cache-Control: public");
    	readfile(APPLICATION_PATH.'/../data/support/' . $supportFile['id']);

		exit();
    }

    public function upload()
    {

		$request = $this->getRequest();
    	$adapter = new Zend_File_Transfer_Adapter_Http();

    	$adapter->setDestination(APPLICATION_PATH.$this->uploads);

    	$files = $adapter->getFileInfo();
    	foreach ($files as $file => $info) {
    		$tmp = rand();
    		$adapter->addFilter('Rename', APPLICATION_PATH . '/../data/tmp_support/' . $tmp);

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
			$data['job_id'] = $this->_request->getParam('job_id');

			$supportUploadFileCount = $this->_request->getParam('support_upload_file_count');
			$data['upload_file_count'] = $supportUploadFileCount;

			$supportUploadKey = $this->_request->getParam('support_upload_key');
			$data['upload_key'] = $supportUploadKey;

    		$data = array_merge($data, array('file_name' => $originalFileName, 'size' => $originalFileSize,'mime_type' => $originalFileMimeType));

    		$mapper  = new Application_Model_SupportFileMapper();
    		$id = $mapper->save($data);

    		// move tmp file to storage location and rename to job_id
    		rename(APPLICATION_PATH.'/../data/tmp_support/' . $tmp, APPLICATION_PATH.'/../data/support/' . $id);

    		// email notification turned off at Rebecca's request (see email to David Carter 22nd August 2012)
    		/*
    		// check if should send email notification
    		$supportUploadKeyCount = $mapper->getUploadKeyCount($supportUploadKey);

    		if ($supportUploadKeyCount == $supportUploadFileCount) {
    			// send email notification
    			$options = array(
					'emailType' => 'supportFileReceived',
    		    	'uploadKey' => $supportUploadKey
    			);
    			$this->_email->send($this->view, $options);
    		}
    		*/

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
}
