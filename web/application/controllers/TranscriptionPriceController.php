<?php

/**
 * TranscriptionPriceController
 *
 */
class TranscriptionPriceController extends App_Controller_Action
{

    protected $_mapper;
    protected $_clientMapper;
    protected $_transcriptionTypeMapper;

    public function init()
    {
        /* Initialize action controller here */
        $this->flashMessenger = $this->_helper->flashMessenger;
        $this->_mapper = new Application_Model_TranscriptionPriceMapper();
        $this->_clientMapper = new Application_Model_ClientMapper();
        $this->_transcriptionTypeMapper = new Application_Model_TranscriptionTypeMapper();
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_processTranscriptionPriceForm();
        $this->_transcriptionPriceData();
    }

    /**
     * Client list pricing info action
     *
     * @return void
     */
    public function clientListAction()
    {
        $clients = $this->_clientMapper->fetchAll();
        $this->view->clients = $clients;
    }

    /**
     * Client Update Transcription Pricing Info Action
     *
     * @return void
     */
    public function clientUpdateAction()
    {
        // action body
        $request = $this->getRequest();
        $clientId = $this->_request->getParam('id');
        $client = $this->_clientMapper->fetchById($clientId);

        $this->_processTranscriptionPriceForm();
        $this->_transcriptionPriceData($clientId);

        $this->view->client = $client;
    }

    /**
     * Client Add Transcription Type and pricing info action
     *
     * @return void
     */
    public function clientTranscriptionTypeAction()
    {
        $request = $this->getRequest();
        $clientId = $this->_request->getParam('id');
        $client = $this->_clientMapper->fetchById($clientId);

        $transcriptionTypeForm = new Application_Form_ClientTranscriptionType();
        $transcriptionTypeForm->getElement('client_id')->setValue($clientId);

        if ($this->getRequest()->isPost())
        {
            $formData     = $this->_request->getPost();
            $errorMessage = '';
            if ($transcriptionTypeForm->isValid($formData))
            {
                $defaultTurnaroundTimeId = $formData['default_turnaround_time'];
                $transcriptionTypeName   = $formData['transcription_type'];

                $data                  = array();
                $data['name']          = $transcriptionTypeName;
                $data['description']   = $formData['description'];
                $data['sort_order']    = $formData['sort_order'];
                $data['turnaround_id'] = $defaultTurnaroundTimeId;
                $data['client_id']     = $clientId;

                // Default turnaround time must be selected for pricing
                if (isset($formData['turnaround_times']) && !empty($formData['turnaround_times']))
                {
                    // Turnaround times to generate prices for
                    $turnaroundTimesForPricing = $formData['turnaround_times'];
                    if (!in_array($defaultTurnaroundTimeId, $turnaroundTimesForPricing))
                    {
                        $errorMessage = 'The default turnaround time must be selected in the turnaround times to generate prices for';
                    }
                    else
                    {
                        // Generate Transcription Type
                        $transcriptionTypeId = $this->_transcriptionTypeMapper->insert($data);

                        // Add prices for the selected turnaround times
                        foreach ($turnaroundTimesForPricing as $turnaroundTimeId)
                        {
                            $data                          = array();
                            $data['transcription_type_id'] = $transcriptionTypeId;
                            $data['turnaround_time_id']    = $turnaroundTimeId;
                            $this->_mapper->insert($data);
                        }
                    }
                }
                else
                {
                    $errorMessage = 'Please select atleast 1 turnaround time to generate prices for';
                }

                if (!empty($errorMessage))
                {
                    $this->flashMessenger->addMessage(array('error' => $errorMessage));
                }
                else
                {
                    $this->flashMessenger->addMessage(array('notice' => 'Transcription Type has been succesfully added'));

                    // Redirect to client pricing
                    $url = $this->view->url(array('controller' => 'transcription-price', 'action' => 'client-update', 'id' => $clientId), null, true);
                    $this->_redirect($url, array('prependBase' => false));
                }
            }
        }

        $this->view->client                = $client;
        $this->view->transcriptionTypeForm = $transcriptionTypeForm;
    }

    /**
     * Validate transcription price form
     *
     * @return void
     */
    protected function _processTranscriptionPriceForm()
    {
        $formErrors = array();
        if ($this->getRequest()->isPost())
        {
            $formData  = $this->_request->getPost();
            $hasErrors = false;
            foreach ($formData as $itemInfoString => $price)
            {
                if (is_numeric($price))
                {
                    if (substr($itemInfoString, 0, 5) === 'price')
                    {
                        $item = explode('_', $itemInfoString);
                        $transcriptionPriceId = $item[1];
                        $this->_mapper->updatePrice($transcriptionPriceId, $price);
                    }
                }
                else
                {
                    $formErrors[$itemInfoString] = 'Please enter a numeric value';
                    $hasErrors = true;
                }
            }

            if ($hasErrors)
            {
                $this->flashMessenger->addMessage(array('error' => "There are errors"));
            }
            else
            {
                $this->flashMessenger->addMessage(array('notice' => "Transcription prices have been succesfully updated"));
            }
        }

        $this->view->formErrors = $formErrors;
    }

    /**
     * Fetch transcription price data
     *
     * @param int $clientId the client to specify pricing info (OPTIONAL)
     *
     * @return void
     */
    protected function _transcriptionPriceData($clientId = null)
    {
        $transcriptionTypes        = $this->_mapper->getTranscriptionTypes($clientId);
        $groupedTranscriptionTypes = array();
        if (!empty($transcriptionTypes))
        {
            foreach ($transcriptionTypes as $transcription)
            {
                $groupedTranscriptionTypes[$transcription['transcriptionName']][] = $transcription;
            }
        }

        $this->view->transcriptionPrices = $groupedTranscriptionTypes;

    }
}

