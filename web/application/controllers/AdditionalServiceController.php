<?php

class AdditionalServiceController extends App_Controller_Action
{
    protected $_additionalServiceMapper;

    public function init()
    {
        $this->flashMessenger = $this->_helper->flashMessenger;
        $this->_additionalServiceMapper = new Application_Model_AdditionalServicesMapper();
    }

    public function indexAction()
    {
        $services = $this->_additionalServiceMapper->fetchAll();
        $this->view->services = $services;
    }

    public function createAction()
    {
    	$form   = new Application_Form_AdditionalService();

    	if ($this->getRequest()->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData))
            {
                $data = array();
                $data['name']        = $formData['name'];
                $data['description'] = $formData['description'];
                $data['price']       = $formData['price'];
                $id = $this->_additionalServiceMapper->insert($data);

                $this->flashMessenger->addMessage(array('notice' => "Service successfully created"));

                // Redirect to edit page
                $url = $this->view->url(array('controller' => 'additional-service', 'action' => 'edit', 'id' => $id), null, true);
                $this->_redirect($url, array('prependBase' => false));
            }
    	}

    	$this->view->form = $form;
    }

    public function editAction()
    {
    	$form   = new Application_Form_AdditionalService();
    	$request = $this->getRequest();
    	$id      = $this->_request->getParam('id');
        $service = $this->_additionalServiceMapper->fetchById($id);

        if ($this->getRequest()->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData))
            {
                $data = array();
                $data['name']        = $formData['name'];
                $data['description'] = $formData['description'];
                $data['price']       = $formData['price'];
                $id = $this->_additionalServiceMapper->updateService($id, $data);

                $this->flashMessenger->addMessage(array('notice' => "Service successfully saved"));
            }
    	}
        else
        {
            $form->populate($service);
        }

        $this->view->service = $service;
        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $id = $this->_request->getParam('id');
        $this->_additionalServiceMapper->deleteService($id);
        $this->flashMessenger->addMessage(array('warning' => "Service has been deleted"));

        // Redirect to list page
        $url = $this->view->url(array('controller' => 'additional-service', 'action' => 'index'), null, true);
        $this->_redirect($url, array('prependBase' => false));
    }
}

