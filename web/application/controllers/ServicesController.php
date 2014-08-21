<?php

class ServicesController extends App_Controller_Action
{

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $serviceModel         = new Application_Model_Service();
        $this->view->services = $serviceModel->fetchAll();
    }

    /**
     * Edit action
     *
     * @return void
     */
    public function editServiceAction()
    {

        $serviceId    = $this->_getParam( 'id', null );
        $serviceModel = new Application_Model_Service();

        if ( $serviceId )
        {
            $service = $serviceModel->fetchRow( $serviceModel->select()->where( 'id = ?', $serviceId ) );
        }
        else
        {
            $service = $serviceModel->createRow();
        }

        if ( $this->getRequest()->isPost() )
        {
            $result = $this->_processEditService( $service );

            if ( false === $result )
            {
                $this->_flash->addMessage( array( 'error' => 'Unable to edit service, please check all values are valid prices' ) );
                $this->_viewEditService( $service );
            }
            else
            {
                $this->_flash->addMessage( array( 'notice' => 'Service edit complete: ' . $service->name ) );

                $url = $this->view->url( array( 'controller' => 'services', 'action' => 'index'), null, true );
                $this->_redirect( $url, array( 'prependBase' => false ) );
            }
        }
        else
        {
            $this->_viewEditService( $service );
        }
    }

    /**
     * Delete service action
     *
     * @return void
     */
    public function deleteServiceAction()
    {
        $serviceId = $this->_request->getParam( 'service_id', null );
        if ( $serviceId )
        {
            $model   = new Application_Model_Service();
            $service = $model->fetchRow( 'id = ' . $serviceId );
            $result  = $service->delete();
            if ( $result )
            {
                $this->_flash->addMessage( array( 'notice' => 'Service deleted' ) );
            }
            else
            {
                $this->_flash->addMessage( array( 'error' => 'Unable to delete service' ) );
            }
        }
        else
        {
            $this->_flash->addMessage( array( 'error' => 'Unable to find service' ) );
        }
        $url = $this->view->url( array( 'controller' => 'services', 'action' => 'index'), null, true );
        $this->_redirect( $url, array( 'prependBase' => false ) );
    }

    /**
     * Delete service group action
     *
     * @return void
     */
    public function deleteGroupAction()
    {
        $serviceId = $this->_request->getParam( 'group_id', null );
        if ( $serviceId )
        {
            $model   = new Application_Model_ServiceGroup();
            $service = $model->fetchRow( 'id = ' . $serviceId );
            $result  = $service->delete();
            if ( $result )
            {
                $this->_flash->addMessage( array( 'notice' => 'Service group deleted' ) );
            }
            else
            {
                $this->_flash->addMessage( array( 'error' => 'Unable to delete service group' ) );
            }
        }
        else
        {
            $this->_flash->addMessage( array( 'error' => 'Unable to find service group' ) );
        }
        $url = $this->view->url( array( 'controller' => 'services', 'action' => 'groups'), null, true );
        $this->_redirect( $url, array( 'prependBase' => false ) );
    }

    /**
     * Process edit service
     *
     * @param Application_Model_Service_Row $service Service Row Object
     *
     * @return Application_Model_Service_Row
     */
    protected function _processEditService( &$service )
    {
        $postData = $this->getRequest()->getPost();
        $form     = new Application_Form_Service();

        if ( !$form->isValid( $postData['service'] ) )
        {
            return false;
        }

        $validatePrices = array_merge(
            $postData['ta-time-price'],
            $postData['speaker-number-price'],
            $postData['speaker-number-typist'],
            $postData['price-modifier-price'],
            $postData['price-modifier-typist']
        );

        foreach( $validatePrices as $price )
        {
            if ( !empty( $price ) && !is_numeric( $price ) )
            {
                return false;
            }
        }

        $service->update( $postData['service'] );

        $taModel = new Application_Model_ServiceTurnaround();
        $taModel->updateServiceTurnaroundTimes( $service->id, $postData['ta-time-price'] );

        $snModel = new Application_Model_ServiceSpeakerNumber();
        $snModel->updateServiceSpeakers( $service->id, $postData['speaker-number-price'], $postData['speaker-number-typist'] );

        $modifierModel = new Application_Model_ServiceModifier();
        $modifierModel->updateServiceModifiers( $service->id, $postData['price-modifier-price'], $postData['price-modifier-typist'] );

        return true;
    }

    /**
     * View edit service page
     *
     * @param Application_Model_Service_Row $service Service Row Object
     *
     * @return void
     */
    protected function _viewEditService( $service )
    {
        $this->view->service = $service;
        $form                = new Application_Form_Service();

        $form->populate(
             array(
                 'name'           => $service->name,
                 'description'    => $service->description,
                 'base_price'     => $service->base_price,
                 'training_code'  => $service->training_code,
                 'typist_grade_1' => $service->typist_grade_1,
                 'typist_grade_2' => $service->typist_grade_2,
                 'typist_grade_3' => $service->typist_grade_3,
                 'typist_grade_4' => $service->typist_grade_4,
                 'typist_grade_5' => $service->typist_grade_5
             )
        );

        $this->view->form = $form;

        $taModel             = new Application_Model_TurnaroundTime();
        $this->view->taTimes = $taModel->fetchAll();

        $snModel                    = new Application_Model_SpeakerNumber();
        $this->view->speakerNumbers = $snModel->fetchAll();

        $modifierModel         = new Application_Model_PriceModifier();
        $this->view->modifiers = $modifierModel->fetchAll();
    }

    /**
     * Groups
     *
     * @return void
     */
    public function groupsAction()
    {
        $groupModel         = new Application_Model_ServiceGroup();
        $this->view->groups = $groupModel->fetchAll();
    }

    /**
     * Edit Group action
     *
     * @return void
     */
    public function editGroupAction()
    {
        $groupId = $this->_getParam( 'id', null );
        $model = new Application_Model_ServiceGroup();

        if ( $groupId )
        {
            $group = $model->fetchRow( $model->select()->where( 'id = ?', $groupId ) );
        }
        else
        {
            $group = $model->createRow();
        }

        if ( $this->getRequest()->isPost() )
        {
            $formData    = $this->getRequest()->getPost();
            $group->name = $formData['name'];
            $group->save();
            $group->updateServices( $formData['service'] );

            $this->_flash->addMessage( array( 'notice' => 'Service edit complete: ' . $group->name ) );
            $url = $this->view->url( array( 'controller' => 'services', 'action' => 'groups'), null, true );
            $this->_redirect( $url, array( 'prependBase' => false ) );
        }
        else
        {
            $serviceModel         = new Application_Model_Service();
            $this->view->services = $serviceModel->fetchAll();
            $this->view->group    = $group;
        }
    }

    /**
     * Edit descriptions
     *
     * @return void
     */
    public function editDescriptionsAction()
    {
        $serviceModel  = new Application_Model_Service();
        $modifierModel = new Application_Model_PriceModifier();

        $services  = $serviceModel->fetchAll();
        $modifiers = $modifierModel->fetchAll();

        if ( $this->getRequest()->isPost() )
        {
            $data                 = $this->getRequest()->getPost();
            $serviceDescriptions  = $data['service_descriptions'];
            $modifierDescriptions = $data['modifier_descriptions'];

            foreach ( $services as $service )
            {
                if ( isset( $serviceDescriptions[$service->id] ) )
                {
                    $service->description = $serviceDescriptions[$service->id];
                    $service->save();
                }
            }
            foreach ( $modifiers as $modifier )
            {
                if ( isset( $modifierDescriptions[$modifier->id] ) )
                {
                    $modifier->description = $modifierDescriptions[$modifier->id];
                    $modifier->save();
                }
            }
            $this->_flash->addMessage( array( 'notice' => 'Descriptions edited' ) );
            $url = $this->view->url( array( 'controller' => 'services' ), null, true );
            $this->_redirect( $url, array( 'prependBase' => false ) );
        }

        $this->view->services  = $services;
        $this->view->modifiers = $modifiers;
    }
}