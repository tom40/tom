<?php

class QuoteGeneratorController extends App_Controller_Action
{

    /**
     * Client ID
     * @var int
     */
    protected $_clientId;

    /**
     * User Identity
     * @var object
     */
    protected $_identity;

    /**
     * Init method
     *
     * @return void
     */
    public function init()
    {
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
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $form = new Application_Form_QuoteGenerator();
        $form->getElement( 'email' )->setValue( $this->_identity->email );

        $serviceMapper = new Application_Model_Service();

        if( null !== $this->_clientId )
        {
            $clientMapper = new Application_Model_ClientMapper();
            $client       = $clientMapper->fetchRow( 'id = ' . $this->_clientId );
            $services     = $client->getAllServices();
        }
        else
        {
            $services = $serviceMapper->fetchAll();
        }

        $emptyValue = array( 0 => '-- Select --' );

        $formServices = App_Db_Manipulate::convertToDropDown( $services, $emptyValue );
        $form->getElement( 'service_id' )->addMultiOptions( $formServices );
        $form->setDefault( 'service_id', $services[0]->id );
        $service = $serviceMapper->fetchRow( 'id = ' . $services[0]->id );

        $speakerNumbers  = $emptyValue + $service->getServiceSpeakerNumbersDropDown();
        $turnAroundTimes = $emptyValue + $service->getServiceTurnaroundTimesDropDown();

        $additionalServices = array();
        foreach ( $services as $serviceRow )
        {
            foreach ( $serviceRow->getServicePriceModifiers() as $serviceModifier )
            {
                $additionalServices[ $serviceModifier->getPriceModifier()->id ] = $serviceModifier->getPriceModifier();
            }
        }

        $this->view->service            = $service;
        $this->view->services           = $services;
        $this->view->additionalServices = $additionalServices;

        $form->getElement( 'speaker_numbers_id' )->addMultiOptions( $speakerNumbers );
        $form->setDefault( 'speaker_numbers_id', 1 );
        $form->getElement( 'turnaround_time_id' )->addMultiOptions( $turnAroundTimes );
        $form->setDefault( 'turnaround_time_id', 1 );

        $this->view->form = $form;
    }

    /**
     * Fetch client service details ajax for link form
     *
     * @return void
     */
    public function fetchServiceDetailsEditAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender( true );

        $serviceId = $this->getRequest()->getParam( 'service_id', null );

        $output = array(
            'turnaround_times' => '-',
            'speaker_numbers'  => '-'
        );

        if( $serviceId > 0 )
        {
            $form       = new Application_Form_AudioJobEdit();
            $emptyValue = array( 0 => '-- Select --' );

            $serviceMapper = new Application_Model_Service();
            $service       = $serviceMapper->fetchRow( 'id = ' . $serviceId );

            $speakerNumbers  = $emptyValue + $service->getServiceSpeakerNumbersDropDown();
            $turnAroundTimes = $emptyValue + $service->getServiceTurnaroundTimesDropDown();

            $form->getElement( 'speaker_numbers_id' )->addMultiOptions( $speakerNumbers );
            $form->setDefault( 'speaker_numbers_id', 1 );
            $form->getElement( 'turnaround_time_id' )->addMultiOptions( $turnAroundTimes );
            $form->setDefault( 'turnaround_time_id', 1 );

            $output['speaker_numbers']  = (string)$form->speaker_numbers_id;
            $output['turnaround_times'] = (string)$form->turnaround_time_id;
            $output['modifiers']        = $this->view->partial( 'job/_partials/_priceModifiers.phtml', 'default', array( 'service'  => $service ) );
        }

        echo json_encode( $output );
    }

    /**
     * Fetch transcription price data
     *
     * @param int $clientId the client to specify pricing info (OPTIONAL)
     *
     * @return array
     */
    protected function _transcriptionPriceData( $clientId = null )
    {
        $mapper                    = new Application_Model_TranscriptionPriceMapper();
        $transcriptionTypes        = $mapper->getTranscriptionTypes( $clientId );
        $groupedTranscriptionTypes = array();
        if( !empty( $transcriptionTypes ) )
        {
            foreach( $transcriptionTypes as $transcription )
            {
                $groupedTranscriptionTypes[$transcription['transcriptionName']][] = $transcription;
            }
        }
        return $groupedTranscriptionTypes;
    }

    /**
     *
     */
    public function generateAction()
    {

        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender( true );

        if( $this->getRequest()->isPost() )
        {
            $form = new Application_Form_QuoteGenerator( $this->_clientId );
            $data = $this->getRequest()->getPost();

            $sendQuoteEmail = ( isset( $data['email_quote'] ) && '1' === $data['email_quote'] ) ? true : false;
            if( $sendQuoteEmail )
            {
                $form->getElement( 'email' )->setRequired( true );
            }

            if( $form->isValid( $data ) )
            {
                $viewData = array();

                $priceCalculator     = new App_AudioJob_PriceCalculator();
                $serviceModel        = new Application_Model_Service();
                $turnaroundTimeModel = new Application_Model_TurnaroundTime();
                $speakerNumbersModel = new Application_Model_SpeakerNumber();

                $service        = $serviceModel->fetchRow( 'id = ' . $data['service_id'] );
                $turnaroundTime = $turnaroundTimeModel->fetchRow( 'id = ' . $data['turnaround_time_id'] );
                $speakerNumbers = $speakerNumbersModel->fetchRow( 'id = ' . $data['speaker_numbers_id'] );

                $priceCalculator->calculateRate(
                    $service->id,
                    $speakerNumbers->id,
                    $turnaroundTime->id,
                    $data['price_modifiers']
                );

                $priceCalculator->setAudioJobDiscount( $data['discount'] );

                if( 'total-hours' === $data['type'] )
                {
                    if( empty( $data['total-time-hours'] ) )
                    {
                        $hours = 0;
                    }
                    else
                    {
                        $hours = $data['total-time-hours'];
                    }
                    if( empty( $data['total-time-minutes'] ) )
                    {
                        $minutes = 0;
                    }
                    else
                    {
                        $minutes = $data['total-time-minutes'];
                    }

                    $length     = ( ( $hours * 60 ) + $minutes );
                    $priceCalculator->setLengthMinutes( $length );
                    $totalPrice = $priceCalculator->calculatePrice();

                    $viewData['totalTime'] = $hours . ' hrs ' . $minutes . ' mins';
                }
                elseif( 'ind-files' === $data['type'] )
                {
                    $totalHours   = 0;
                    $totalMinutes = 0;
                    $totalPrice   = 0;
                    $files        = array();

                    foreach( $data['ind-time-hours'] as $key => $hours )
                    {
                        if( empty( $hours ) )
                        {
                            $totalHours += 0;
                        }
                        else
                        {
                            $totalHours += $hours;
                        }
                        $minutes = $data['ind-time-minutes'][$key];
                        if( empty( $minutes ) )
                        {
                            $totalMinutes += 0;
                        }
                        else
                        {
                            $totalMinutes += $minutes;
                        }
                        $fileLength = ( ( $hours * 60 ) + $minutes ) ;

                        $priceCalculator->setLengthMinutes( $fileLength );
                        $filePrice   = $priceCalculator->calculatePrice();
                        $totalPrice += $filePrice;
                        $files[$key] = array(
                            'duration'   => $hours . ' hrs ' . $minutes . ' mins',
                            'price'      => $filePrice,
                            'totalPrice' => $filePrice * 1.2
                        );
                    }
                    $viewData['files'] = $files;
                }

                if( '1' === $data['what'] )
                {
                    $viewData['what'] = $data['what_other'];
                }
                else
                {
                    $viewData['what'] = $data['what'];
                }

                $viewData['price']             = $totalPrice;
                $viewData['totalPrice']        = $totalPrice * 1.2;
                $viewData['transcriptionType'] = $service->name;
                $viewData['turnAround']        = $turnaroundTime->name;
                $viewData['discount']          = $data['discount'];

                $this->view->viewData = $viewData;

                if( $sendQuoteEmail )
                {
                    $this->_emailQuote( $viewData, $data['email'] );
                }

                $output           = array();
                $outputHtml       = $this->view->render( 'quote-generator/generated.phtml' );
                $output['html']   = $outputHtml;
                $output['status'] = 'ok';
                echo json_encode( $output );
            }
            else
            {
                $output           = array();
                $output['html']   = $form->getMessages();
                $output['status'] = 'invalid';
                echo json_encode( $output );
            }
        }
    }

    /**
     * Email quote
     *
     * @param array  $viewData Key value pairs for view file
     * @param string $email    Email address
     *
     * @return void
     */
    protected function _emailQuote( $viewData, $email )
    {
        $quoteEmail = new App_Mail_QuoteEmail;
        $quoteEmail->setView( $this->view )
                   ->setViewData( $viewData )
                   ->addReceiver( $email )
                   ->sendMail();
    }

    public function transTurnaroundAjaxAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender( true );

        $response = array();
        $id       = $this->_request->getParam( 'id' );
        $mapper   = new Application_Model_TranscriptionPriceMapper();
        $items    = $mapper->fetchTurnaroundTimesForQuoteGenerator( $id );
        array_unshift( $items, array( 'key' => '0', 'value' => '-- select --' ) );

        $tMapper           = new Application_Model_TranscriptionTypeMapper();
        $transcriptionType = $tMapper->fetchById( $id );
        $turnaroundTimeId  = $transcriptionType['turnaround_id'];

        $response['message']  = $items;
        $response['selected'] = $turnaroundTimeId;
        $response['status']   = 'OK';

        echo json_encode( $response );
    }
}