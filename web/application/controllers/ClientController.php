<?php

class ClientController extends App_Controller_Action
{
    public function listAllAction()
    {
    	// action body
    	$clients = new Application_Model_ClientMapper();
    	$this->view->clients = $clients->fetchAll();
    }

    /**
     * Create client action
     *
     * @return void
     */
    public function createAction()
    {
    	// action body
    	$request      = $this->getRequest();
    	$form         = new Application_Form_ClientCreate();
        $clientMapper = new Application_Model_ClientMapper();

    	if ($this->getRequest()->isPost())
        {
    		if ($form->isValid($request->getPost()))
            {
                $data   = $this->getRequest()->getPost();
                $client = $clientMapper->createRow();
                $this->_updateClient( $client, $data );
                $result = $this->_updateClient( $client, $data );
                if ( $result )
                {
                    $this->_helper->redirector->gotoSimple('list-all', 'client', 'default');
                }
	        }
            else
            {
	    		$this->_helper->FlashMessenger(array('error' => 'Form not correctly completed. Please see item(s) in red text below.'));
	    	}
		}

        $serviceGroupMapper        = new Application_Model_ServiceGroup();
        $this->view->serviceGroups = $serviceGroupMapper->fetchAll();

        $serviceMapper        = new Application_Model_Service();
        $this->view->services = $serviceMapper->getServicesFilteredGroups();

		$this->view->form = $form;
    }

    public function editAction()
    {
    	// action body
    	$request = $this->getRequest();
    	$id      = $this->_request->getParam('id');

    	$clientMapper = new Application_Model_ClientMapper();
    	$form         = new Application_Form_ClientEdit();

    	if ($this->getRequest()->isPost())
        {
    		if ($form->isValid($request->getPost()))
            {
    			$data = $this->getRequest()->getPost();

                if ( isset( $data['id'] ) )
                {
                    $client = $clientMapper->fetchRow( $clientMapper->select()->where( 'id = ?', $data['id'] ) );
                }
                else
                {
                    $client = $clientMapper->createRow();
                }

                $result = $this->_updateClient( $client, $data );

                if ( $result )
                {
                    $this->_helper->redirector->gotoSimple('list-all', 'client', 'default');
                }
    		}
            else
            {
    			$this->_helper->FlashMessenger(array('error' => 'Form not correctly completed. Please see item(s) in red text below.'));
    		}
    	}
        else
        {
    		$client = $clientMapper->fetchRow( $clientMapper->select()->where( 'id = ?', $id ) );
    		$form->populate( $client->toArray() );
    	}

        $serviceGroupMapper        = new Application_Model_ServiceGroup();
        $this->view->serviceGroups = $serviceGroupMapper->fetchAll();

        $serviceMapper        = new Application_Model_Service();
        $this->view->services = $serviceMapper->getServicesFilteredGroups( $client->getServiceGroups() );

    	$this->view->data = $client;
    	$this->view->form = $form;
    }

    /**
     * Update client
     *
     * @param Application_Model_Client_Row $client Client row object
     * @param array                        $data   Form input data
     *
     * @return bool
     */
    public function _updateClient( $client, $data )
    {
        $result = false;

        $client->name      = $data['name'];
        $client->telephone = $data['telephone'];
        $client->address   = $data['address'];
        $client->postcode  = $data['postcode'];
        $client->comments  = $data['comments'];
        $client->discount  = $data['discount'];
        $client->save();
        $id = $client->id;

        if ($id > 0)
        {
            if ( empty( $data['additiona_services'] ) && empty( $data['service_groups'] ) )
            {
                $this->_helper->FlashMessenger(array('error' => 'A client must be associcated with at least one service or service group'));
            }
            else
            {
                $client->updateAdditionalServices( $data['additional_services'] );
                $clientGroups = ( isset( $data['service_groups'] ) ) ? $data['service_groups'] : null;
                $client->updateServiceGroups( $clientGroups );
                $this->_helper->FlashMessenger('Record Saved!');
                $result = true;
            }
        }
        else
        {
            $this->_helper->FlashMessenger(array('error' => 'Record Not Saved!'));
        }
        return $result;
    }

    /**
     * AJAX method to update which additional services can be viewed
     *
     * @return void
     */
    public function showAdditionalServicesAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $id     = $this->_request->getParam( 'id', null );
        $client = null;

        if ( null !== $id )
        {
            $clientMapper = new Application_Model_ClientMapper();
            $client       = $clientMapper->fetchRow( $clientMapper->select()->where( 'id = ?', $id ) );
        }

        $serviceMapper        = new Application_Model_Service();
        $data                 = $this->getRequest()->getPost();
        $services = $serviceMapper->getServicesFilteredGroups( $data['service_groups'] );
        $output   = array(
            'html' => $this->view->partial( 'client/_partials/_services.phtml', 'default', array( 'services' => $services, 'data' => $client ) )
        );

        echo json_encode( $output );
    }

    public function fetchUsersByClientIdAction()
    {
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

    	$clientId = $this->_request->getParam('client_id');
    	$clientsUserMapper = new Application_Model_ClientsUserMapper();
    	$data = $clientsUserMapper->fetchByClientIdForDropdown($clientId);

    	$output = array();
    	$output['status'] = 'ok';
    	$output['data']   = $data;

    	echo json_encode($output);

    }

    /**
     * Fetch the client users. This is used when showing client users to grant access to on a project
     * returns empty array if only 1 user is found as this would be the already selected primary user
     *
     * @return void
     */
    public function fetchClientUsersAction()
    {
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);

        $outputStatus = 'ok';

    	$clientId = $this->_request->getParam('client_id');

        // Check if this is called from edit action
        $isEditAction = $this->_request->getParam('is_edit');
        $jobId        = $this->_request->getParam('job_id');
        if (!empty($isEditAction) && (1 == $isEditAction) && !empty($jobId))
        {
            $this->view->isEditAction = true;
            $this->view->jobId        = $jobId;
        }

    	$clientsUserMapper = new Application_Model_ClientsUserMapper();
    	$clientUsers       = $clientsUserMapper->fetchByClientId($clientId);

        $this->view->clientUsers = $clientUsers;
        $outputHtml              = $this->view->render('job/_clientUserColleagues.phtml');
        $output['status']        = $outputStatus;
    	$output['html']          = $outputHtml;
    	echo json_encode($output);
    }

    /**
     * Update client service base rates
     *
     * @return void
     */
    public function clientServiceBaseRatesAction()
    {
        $clientId     = $this->_request->getParam('client_id');
        $clientMapper = new Application_Model_ClientMapper();

        $client = $clientMapper->fetchRow( 'id = ' . $clientId );
        $this->view->client = $client;

        if ( $this->getRequest()->isPost() )
        {
            $data = $this->getRequest()->getPost();

        }
        else
        {

        }
    }
}