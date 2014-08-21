<?php

/**
 * TranscriptionTypeController
 *
 * @todo: complete
 */
class TranscriptionTypeController extends App_Controller_Action
{
    const EMAIL_TRANSCRIBER = 'transcriber';
    const EMAIL_TYPIST      = 'typist';

    private $_groupModel;
    private $_messageTitle;
    private $_messageBody;

    protected $_mapper;

    public function init()
    {
        /* Initialize action controller here */
        $this->_groupModel = new Application_Model_Group();
        $this->flashMessenger = $this->_helper->flashMessenger;
        $this->_mapper = new Application_Model_TranscriptionTypeMapper();
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
    	$transcriptionTypes = $this->_mapper->fetchAll();

        foreach ($transcriptionTypes as $key => $transcriptionType)
        {
            $transcriptionTypes[$key]['canDelete'] = $this->_mapper->canDelete(
                $transcriptionType['id']
            );
        }
    	$this->view->transcriptionTypes = $transcriptionTypes;
    }

    /**
     * Delete a transaction type
     *
     * @return void
     */
    public function deleteAction()
    {
    	if ($this->getRequest()->isPost())
        {
            $formData = $this->_request->getPost();
            // Reset the audio job/job transcription type id
            $audioJobMapper = new Application_Model_AudioJobMapper();
            $jobMapper      = new Application_Model_JobMapper();
            $typistPayrate  = new Application_Model_TranscriptionTypistPayrateMapper();

            $jobMapper->resetArchivedJobTranscriptionTypeId($formData['transaction-type-id']);
            $audioJobMapper->resetArchivedAudioJobTranscriptionTypeId($formData['transaction-type-id']);

            $this->_mapper->delete("id = '" . $formData['transaction-type-id'] . "'");
            $typistPayrate->deleteTranscriptionPayrates($formData['transaction-type-id']);
            $this->flashMessenger->addMessage(array('notice' => "Transcription type deleted"));

        }
        else
        {
            $this->flashMessenger->addMessage(array('error' => "Could not delete transcription type"));
        }
        $url = $this->view->url(array('controller' => 'transcription-type'), null, true);
        $this->_redirect($url);
    }

    public function editAction()
    {
    	$transcriptionTypeId = $this->_request->getParam('id');
    	$form                = new Application_Form_TranscriptionType();
        $transcriptionType   = $this->_mapper->fetchById($transcriptionTypeId);
        $taMapper            = new Application_Model_TranscriptionPriceMapper;
        $paygradeMapper      = new Application_Model_TranscriptionTypistPayrateMapper();
        $paygrades           = $this->_getPayrateGrades();

        $taTimes = $taMapper->getTranscriptionTypePrices($transcriptionTypeId);

        foreach ($taTimes as $taTime)
        {
            $turnaroundTimes[] = $taTime['turnaround_time_id'];
        }

        $values = array(
            'name'             => $transcriptionType['name'],
            'description'      => $transcriptionType['description'],
            'turnaround_id'    => $transcriptionType['turnaround_id'],
            'training_code'    => $transcriptionType['training_code'],
            'turnaround_times' => $turnaroundTimes,
        );

        $paygradeData = $paygradeMapper->fetchTranscriptionPayrates($transcriptionTypeId);
        foreach ($paygradeData as $pgId => $pgValue)
        {
            $values['payrate' . $pgId] = $pgValue;
        }

    	if ($this->getRequest()->isPost())
        {
            $formData = $this->_request->getPost();
    		if ($form->isValid($formData))
            {
                $data = array(
                    'name'          => $formData['name'],
                    'description'   => $formData['description'],
                    'training_code' => $formData['training_code'],
                    'turnaround_id' => $formData['turnaround_id']
                );
                $this->_mapper->update($data, 'id = ' . $transcriptionTypeId);
                $taMapper->setPriceRows($transcriptionTypeId, $formData['turnaround_times']);

                $paygradeData   = array();
                foreach (array_keys($paygrades) as $pgId)
                {
                    $paygradeData[$pgId] = $formData['payrate' . $pgId];
                }
                $paygradeMapper->updateTranscriptionPayrateData($transcriptionTypeId, $paygradeData);

                $url = $this->view->url(array('controller' => 'transcription-type'), null, true);
                $this->flashMessenger->addMessage(array('notice' => "Transcription type edited"));
                $this->_redirect($url);
            }
            else
            {
                $values = $formData;
                foreach (array_keys($paygrades) as $pgId)
                {
                    $values['payrate' . $pgId] = $formData['payrate' . $pgId];
                }
            }
        }

        $form->populate($values);
        $this->view->payrateGrades     = $paygrades;
        $this->view->form              = $form;
        $this->view->transcriptionType = $transcriptionType;
    }

    /**
     * Add a transcription price
     *
     * return @void
     */
    public function addAction()
    {
        $form      = new Application_Form_TranscriptionType();
        $taMapper  = new Application_Model_TranscriptionPriceMapper;
        $values    = array();
        $paygrades = $this->_getPayrateGrades();

        $action = $this->view->url(array('controller' => 'transcription-type', 'action' => 'add'), null, true);
        $form->setAction($action);

    	if ($this->getRequest()->isPost())
        {
            $formData = $this->_request->getPost();
    		if ($form->isValid($formData))
            {
                $data = array(
                    'name'          => $formData['name'],
                    'description'   => $formData['description'],
                    'turnaround_id' => $formData['turnaround_id']
                );
                $transcriptionTypeId = $this->_mapper->insert($data);
                $taMapper->setPriceRows($transcriptionTypeId, $formData['turnaround_times']);

                $paygradeMapper = new Application_Model_TranscriptionTypistPayrateMapper();
                $paygradeData   = array();
                foreach (array_keys($paygrades) as $pgId)
                {
                    $paygradeData[$pgId] = $formData['payrate' . $pgId];
                }
                $paygradeMapper->updateTranscriptionPayrateData($transcriptionTypeId, $paygradeData);

                $url = $this->view->url(array('controller' => 'transcription-type'), null, true);
                $this->flashMessenger->addMessage(array('notice' => "Transcription type added"));
                $this->_redirect($url);
            }
            else
            {
                $this->_helper->FlashMessenger(array('error' => 'Form not correctly completed. Please see item(s) in red text below.'));
                $values = $formData;
                foreach (array_keys($paygrades) as $pgId)
                {
                    $values['payrate' . $pgId] = $formData['payrate' . $pgId];
                }
            }
        }
        $form->populate($values);
        $this->view->form          = $form;
        $this->view->payrateGrades = $paygrades;
        $this->render('edit');
    }

    /**
     * Ajax fetching the turnaround time id that belongs to the selected
     * transcription type. Used for pre-selecting turnaround time.
     *
     * @return void
     */
    public function transTurnaroundAjaxAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $status = 'OK';
        $message = '';

        $response = array();

        $transcriptionTypeId = $this->_request->getParam('id');
        if (!empty($transcriptionTypeId))
        {
            $transcriptionType = $this->_mapper->fetchById($transcriptionTypeId);
            $turnaroundTimeId  = $transcriptionType['turnaround_id'];
            $message           = $turnaroundTimeId;
        }

        $response['status'] = $status;
        $response['message'] = $message;
        echo json_encode($response);
    }

    /**
     * Get typist pay rate grades
     *
     * @return array
     */
    protected function _getPayrateGrades()
    {
        $payrateMapper = new Application_Model_TypistPayrateMapper();
        return $payrateMapper->getPayrateGradeArray();
    }

}

