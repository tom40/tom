<?php
/**
 * Created by JetBrains PhpStorm.
 * User: joemiddleton
 * Date: 20/08/2013
 * Time: 13:22
 * To change this template use File | Settings | File Templates.
 */

class StaffInvoiceController extends App_Controller_Action
{
    /**
     *
     */
    protected $_group;

    /**
     *
     */
    protected $_invoiceId;

    /**
     *
     */
    protected $_userId;

    /**
     * Init method
     *
     * @return void
     */
    public function init()
    {
        $this->_userId       = Zend_Auth::getInstance()->getIdentity()->id;
        $this->view->isStaff = $this->_isStaff();
        parent::init();
        $this->view->isAdmin = $this->_isAdmin();
    }

    /**
     * Get Group
     *
     * @return int
     */
    protected function _getGroup()
    {
        return Zend_Auth::getInstance()->getIdentity()->acl_group_id;
    }

    /**
     *
     */
    protected function _isStaff()
    {
        $group = $this->_getGroup();
        return ( Application_Model_UserMapper::STAFF_ACL_GROUP_ID === $group  );
    }

    /**
     *
     */
    protected function _isAdmin()
    {
        return $this->_acl->isAdmin();
    }

    /**
     *
     */
    public function generateInvoiceAction()
    {
        $mapper = new Application_Model_StaffInvoiceMapper();
        $form   = new Application_Form_GenerateInvoice();

        if( $this->getRequest()->isPost() )
        {
            $data   = $this->getRequest()->getPost();
            $userId = $this->_userId;

            $message = '';
            if ( $this->_isAdmin() )
            {
                $form->getElement( 'staff_user' )->setRequired();
                $message .= ' and select a user';
            }

            if ( !$form->isValid( $data ) )
            {
                $this->_flash->addMessage( array( 'error' => 'Please enter a start and end date' . $message ) );
            }
            else
            {
                if ( $this->_isAdmin() )
                {
                    $userId = $data['staff_user'];
                }

                $invoice = $mapper->generateInvoice( $userId, $data );

                if ( $invoice )
                {
                    $this->_flash->addMessage( array( 'notice' => 'Invoice Generated' ) );
                    $this->_redirect( '/en/default/staff-invoice/view-invoice/id/' . $invoice['id'] );
                }
                else
                {
                    $this->_flash->addMessage( array( 'error' => 'Unable to generate Invoice there are no audio jobs for this period' ) );
                }
            }
        }

        $userMapper       = new Application_Model_UserMapper();
        $this->view->form = $form;

        if ( $this->_isAdmin() )
        {
            $this->view->staffUsers = $userMapper->getAllTypistsAndProofreaders();
        }
        else
        {
            $user                        = $userMapper->fetchRow( 'id = "' . $this->_userId .'"' );
            $this->view->proofReaderOnly = ( $user->getTypist() === false && $user->getProofReader() !== false );
            $this->proofReaderOnly;
        }

    }

    /**
     *
     */
    public function viewInvoiceAction()
    {
        $invoiceId = $this->getRequest()->getParam( 'id' );
        $mapper    = new Application_Model_StaffInvoiceMapper();
        $invoice   = $mapper->fetchRow( 'id = ' . $invoiceId );

        if ( !$this->_isAdmin() && $this->_userId !== $invoice->user_id )
        {
            $this->_redirect( '/' );
        }

        $userMapper             = new Application_Model_UserMapper();
        $user                   = $userMapper->fetchRow( 'id = "' . $this->_userId .'"' );
        $this->view->typistOnly = ( (bool) $user->getTypist() === true && (bool) $user->getProofReader() === false );
        $this->view->invoice    = $invoice;
    }

    /**
     * Download Invoice
     *
     * @return void
     */
    public function downloadInvoiceAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender( true );

        $invoiceId = $this->getRequest()->getParam( 'id' );
        $mapper    = new Application_Model_StaffInvoiceMapper();
        $invoice   = $mapper->getInvoice( $invoiceId );
        $generate  = $this->getRequest()->getParam( 'generate', null);

        if ( null === $generate )
        {
            $invoice->viewPdf( $this->view );
        }
        else
        {
            $invoice->generatePdf( $this->view, true );
        }
    }

    /**
     *
     */
    public function viewInvoicesAction()
    {
        $invoiceMapper = new Application_Model_StaffInvoiceMapper();
        if( $this->_isAdmin() )
        {
            $template             = 'view-invoices';
            $status               = $this->getRequest()->getParam( 'status', null );
            $this->view->invoices = $invoiceMapper->getInvoices( null, $status );

            if ( null !== $status )
            {
                $this->view->transitions   = Application_Model_StaffInvoiceMapper::getAdminTransitions( $status );
                $this->view->currentStatus = $status;
            }
        }
        else
        {
            $template             = 'view-invoices-staff';
            $this->view->invoices = $invoiceMapper->getInvoices( $this->_userId );
        }

        $this->render( $template );
    }

    /**
     * Submit comment
     *
     * @return void
     */
    public function submitCommentAction()
    {
        $invoiceId = $this->getRequest()->getParam( 'id', null );
        if ( $invoiceId && $this->getRequest()->isPost() )
        {
            $mapper  = new Application_Model_StaffInvoiceMapper();
            $invoice = $mapper->getInvoice( $invoiceId );
            $data    = $this->getRequest()->getPost();
            if ( $invoice->comment( $data['invoice_comment'], $this->_userId ) )
            {
                $this->_flash->addMessage( array( 'notice' => 'Comment posted' ) );
            }
            else
            {
                $this->_flash->addMessage( array( 'error' => 'There was an error posting the comment' ) );
            }
        }
        else
        {
            $this->_flash->addMessage( array( 'error' => 'Insufficient data to post comment' ) );
        }
        $this->_redirectToInvoice( $invoiceId );
    }

    /**
     * Transition Invoice
     *
     * @return void
     */
    public function transitionInvoiceAction()
    {
        $invoiceId = $this->getRequest()->getParam( 'id', null );
        if ( $invoiceId && $this->getRequest()->isPost() )
        {
            $data    = $this->getRequest()->getPost();
            $mapper  = new Application_Model_StaffInvoiceMapper();
            $invoice = $mapper->getInvoice( $invoiceId );
            if ( $invoice->transition( $data['status_id'] ) )
            {
                $this->_flash->addMessage( array( 'notice' => 'Invoice status edited' ) );
                $this->_sendTransitionEmail( $invoice );
            }
            else
            {
                $this->_flash->addMessage( array( 'error' => 'There was an error editing the invoice status' ) );
            }
        }
        else
        {
            $this->_flash->addMessage( array( 'error' => 'Insufficient data to edit the invoice status' ) );
        }
        $this->_redirectToInvoice( $invoiceId );
    }

    /**
     * Transition multiple invoices
     */
    public function transitionMultipleInvoicesAction()
    {
        if ( $this->getRequest()->isPost() )
        {
            $data = $this->getRequest()->getPost();
            if ( is_array( $data['invoice-id'] ) && count( $data['invoice-id'] ) > 0 )
            {
                $mapper  = new Application_Model_StaffInvoiceMapper();
                $success = array();
                $errors  = array();

                foreach ( $data['invoice-id'] as $invoiceId )
                {
                    $invoice = $mapper->getInvoice( $invoiceId );
                    if ( $invoice->transition( $data['status_id'] ) )
                    {
                        $success[] = true;
                        $this->_sendTransitionEmail( $invoice );
                    }
                    else
                    {
                        $errors[] = true;
                    }
                }
            }
            else
            {
                $this->_flash->addMessage( array( 'error' => 'Insufficient data to edit the invoice statuses' ) );
            }

            if ( count( $success ) > 0 )
            {
                $this->_flash->addMessage( array( 'notice' => 'Invoices successfully transitioned: ' . count( $success ) ) );
            }
            if ( count( $errors ) > 0 )
            {
                $this->_flash->addMessage( array( 'error' => 'Invoices not successfully transitioned: ' . count( $errors ) ) );
            }
            $this->_redirect( '/en/default/staff-invoice/view-invoices/status/' . $data['current_status_id'] );
        }
        $this->_redirect( '/en/default/staff-invoice/view-invoices' );
    }

    /**
     * Send invoice status transition email
     *
     * @param Application_Model_StaffInvoice_Row $invoice Invoice object
     *
     * @return bool
     */
    public function _sendTransitionEmail( $invoice )
    {
        $invoiceStatusEmail = new App_Mail_StaffInvoiceEmail();
        $invoiceStatusEmail->setView( $this->view );
        $invoiceStatusEmail->setReceiver( $invoice->getUser()->email );
        $invoiceStatusEmail->setData( array( 'invoice' => $invoice ) );
        return $invoiceStatusEmail->sendMail();
    }

    /**
     * Delete invoice
     *
     * @return void
     */
    public function deleteInvoiceAction()
    {
        if ( $this->getRequest()->isPost() )
        {
            $data    = $this->getRequest()->getPost();
            $mapper  = new Application_Model_StaffInvoiceMapper();
            $invoice = $mapper->getInvoice( $data['invoice_id'] );
            if ( $invoice->canDelete( $this->_isStaff() ) && $invoice->deleteInvoice() )
            {
                $this->_flash->addMessage( array( 'notice' => 'Invoice deleted' ) );
            }
            else
            {
                $this->_flash->addMessage( array( 'error' => 'Unable to delete invoice' ) );
            }
        }
        else
        {
            $this->_flash->addMessage( array( 'error' => 'Unable to delete invoice' ) );
        }
        $this->_redirect( '/en/default/staff-invoice/view-invoices' );
    }

    /**
     * Edit record
     *
     * @return void
     */
    public function editRecordAction()
    {
        $output = array();
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $form              = new Application_Form_StaffInvoiceRecord();
        $recordId          = $this->getRequest()->getParam( 'record-id', null );
        $recordIdArray     = explode( '-', $recordId );
        $this->view->count = count( $recordIdArray );
        $this->view->id    = $recordId;
        $recordMapper      = new Application_Model_StaffInvoiceRecordMapper();

        if ( $this->getRequest()->isPost() )
        {
            $results = array();
            foreach ( $recordIdArray as $idValue )
            {
                $results[] = $this->_saveInvoiceRecord( $idValue );
            }

            $message = 'edited';
            if ( null === $recordId )
            {
                $message = 'added';
            }

            $success = 0;
            $fail    = 0;
            foreach( $results as $result )
            {
                if ( $result['result'] )
                {
                    $success++;
                }
                else
                {
                    $fail++;
                }
            }

            if ( $success > 0 )
            {
                $recordString = ( 1 === $success ) ? 'record' : 'records';
                $this->_flash->addMessage( array( 'notice' => $success . ' ' . $recordString . ' ' . $message ) );
            }
            if ( $fail > 0 )
            {
                $this->_flash->addMessage( array( 'error' => $fail . ' ' . $recordString . ' ' . $message ) );
            }

            $output['status'] = 'ok';
        }
        else
        {
            if ( null !== $recordId )
            {
                $recordArray    = array();
                $serviceRecords = 0;
                $transRecords   = 0;

                foreach ( $recordIdArray as $value )
                {
                    $where         = $recordMapper->getAdapter()->quoteInto( 'id = ?', $value );
                    $record        = $recordMapper->fetchRow( $where );

                    if ( $record->hasService() )
                    {
                        $serviceRecords++;
                    }
                    else
                    {
                        $transRecords++;
                    }

                    $recordArray[] = $record;
                }

                $record = $recordArray[0];

                $data = array(
                    'name'           => $record->getName(),
                    'minutes_worked' => $record->minutes_worked,
                    'pay_per_minute' => $record->pay_per_minute,
                    'total'          => $record->total
                );

                if ( $transRecords > 0 && $serviceRecords > 0 )
                {
                    $this->view->hideServices = true;
                }
                else
                {
                    if ( $record->hasService() )
                    {
                        $serviceMapper = new Application_Model_Service();
                        $services      = App_Db_Manipulate::convertToDropDown( $serviceMapper->fetchAll() );

                        $form->getElement( 'service_id' )->addMultiOptions( $services );

                        $speakerNumbers  = $record->getService()->getServiceSpeakerNumbersDropDown();
                        $turnAroundTimes = $record->getService()->getServiceTurnaroundTimesDropDown();

                        $form->getElement( 'speaker_numbers_id' )->addMultiOptions( $speakerNumbers );
                        $form->getElement( 'turnaround_time_id' )->addMultiOptions( $turnAroundTimes );

                        $data['service_id']         = $record->service_id;
                        $data['turnaround_time_id'] = $record->turnaround_time_id;
                        $data['speaker_numbers_id'] = $record->speaker_numbers_id;
                    }
                    else
                    {
                        $taMapper = new Application_Model_TranscriptionTypeMapper();
                        $items    = $taMapper->fetchAllForDropdown($this->_clientId);
                        if (count($items) > 1)
                        {
                            array_unshift($items, array('key' => '0', 'value' => '-- select --'));
                        }
                        $form->getElement('transcription_type_id')->addMultiOptions($items);
                        $data['transcription_type_id'] = $record->transcription_type_id;
                    }
                }

                $form->populate( $data );
                $this->view->record   = $record;
                $this->view->records  = $recordArray;
                $this->view->form->id = $recordId;
                $this->view->isAdHoc  = $record->isAdHoc();
            }
            else
            {
                $this->view->isAdHoc = true;
            }

            $this->view->invoiceId = $this->getRequest()->getParam( 'invoice-id' );
            $this->view->form      = $form;
            $output                = array(
                'html'   => $this->view->render('staff-invoice/edit-record.phtml'),
                'status' => 'ok'
            );

        }
        echo json_encode($output);
    }

    /**
     * Save invoice record
     *
     * @param int $recordId Record ID
     *
     * @return bool
     */
    protected function _saveInvoiceRecord( $recordId )
    {
        $recordMapper = new Application_Model_StaffInvoiceRecordMapper();
        $params       = $this->getRequest()->getParams();

        if ( empty( $recordId ) )
        {
            $record                 = $recordMapper->createRow();
            $record->ad_hoc         = 1;
            $record->name           = $params['name'];
            $record->invoice_id     = $params['invoice_id'];
            $record->created_date   = date( 'Y-m-d H:i:s', time() );
            $record->pay_per_minute = $params['pay_per_minute'];
            $record->minutes_worked = $params['minutes_worked'];
        }
        else
        {
            $where  = $recordMapper->getAdapter()->quoteInto( 'id = ?', $recordId );
            $record = $recordMapper->fetchRow( $where );

            if ( !empty( $params['minutes_worked'] ) )
            {
                $record->minutes_worked = $params['minutes_worked'];
            }

            $record->pay_per_minute = $params['pay_per_minute'];

            if ( isset( $params['transcription_type_id'] ) )
            {
                $record->transcription_type_id = $params['transcription_type_id'];
            }
            elseif ( isset( $params['service_id'] ) )
            {
                $record->service_id         = $params['service_id'];
                $record->turnaround_time_id = $params['turnaround_time_id'];
                $record->speaker_numbers_id = $params['speaker_numbers_id'];
                if ( isset( $params['price_modifiers'] ) && is_array( $params['price_modifiers'] ) )
                {
                    $record->price_modifiers = serialize( $params['price_modifiers'] );
                }
            }
        }


        if ( !empty( $params['total'] ) )
        {
            $record->total = $params['total'];
        }

        $result = $record->save();

        if ( $result && !empty( $params['calculate_price'] ) )
        {
            $record->updatePayRate();
        }

        return array(
            'result'  => $result,
            'message' => $message
        );
    }

    /**
     * Accuracy action
     *
     * @return void
     */
    public function accuracyAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ( $this->getRequest()->isPost() )
        {
            $params       = $this->getRequest()->getParams();
            $recordId     = $this->getRequest()->getParam( 'recordId' );
            $recordMapper = new Application_Model_StaffInvoiceRecordMapper();
            $where        = $recordMapper->getAdapter()->quoteInto( 'id = ?', $recordId );
            $record       = $recordMapper->fetchRow( $where );

            $record->inaccurate = $params['accuracy'];

            if ( '0' == $params['accuracy'] )
            {
                $accuracy = 'accurate';
            }
            else
            {
                $accuracy = 'in-accurate';
            }

            if ( $record->save() )
            {
                $this->_flash->addMessage( array( 'notice' => 'Record marked as ' . $accuracy ) );
            }
            else
            {
                $this->_flash->addMessage( array( 'error' => 'Could not mark record as ' . $accuracy ) );
            }
        }
        else
        {
            $this->_flash->addMessage( array( 'error' => 'Could not change accuracy of record' ) );
        }
    }

    /**
     *
     */
    public function createAdHocAction()
    {
        if ( $this->getRequest()->isPost() )
        {
            $params       = $this->getRequest()->getParams();
            $invoiceId    = $this->getRequest()->getParam( 'invoice-id' );
            $recordMapper = new Application_Model_StaffInvoiceRecordMapper();
            $newRecord    = $recordMapper->createRow();

            $newRecord->invoice_id = $invoiceId;
            $newRecord->pay_per_minute = $params['pay_per_minute'];
            $newRecord->minutes_worked = $params['minutes_worked'];

            if ( !empty( $params['total'] ) )
            {
                $newRecord->total = $params['total'];
            }

            if ( !empty( $params['total'] ) )
            {
                $newRecord->total = $params['total'];
            }

            if ($newRecord->save() )
            {
                $this->_flash->addMessage( array( 'notice' => 'Ad-hoc record created' ) );
            }
            else
            {
                $this->_flash->addMessage( array( 'error' => 'Could not create ad-hoc record' ) );
            }
            $this->_redirectToInvoice( $invoiceId );
        }
        $this->_redirect( '/en/default/staff-invoice/view-invoices' );
    }

    /**
     *
     */
    public function deleteRecordAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ( $this->getRequest()->isPost() )
        {
            $params   = $this->getRequest()->getPost();
            $recordId = $params['recordId'];
            $action   = $params['action'];

            $recordMapper = new Application_Model_StaffInvoiceRecordMapper();
            $where        = $recordMapper->getAdapter()->quoteInto( 'id = ?', $recordId );
            $record       = $recordMapper->fetchRow( $where );

            if ( 'remove' === $action )
            {
                if ( $record->delete() )
                {
                    $this->_flash->addMessage( array( 'notice' => 'Record removed' ) );
                }
                else
                {
                    $this->_flash->addMessage( array( 'error' => 'Unable to remove record' ) );
                }
            }
            else
            {
                $record->deleted = 1;
                if ( $record->save() )
                {
                    $this->_flash->addMessage( array( 'notice' => 'Record deleted' ) );
                }
                else
                {
                    $this->_flash->addMessage( array( 'error' => 'Unable to delete record' ) );
                }
            }
        }
        else
        {
            $this->_flash->addMessage( array( 'error' => 'Unable to delete record' ) );
        }
    }

    /**
     *
     */
    public function removeRecordAction()
    {
        if ( $this->getRequest()->isPost() )
        {
            $recordId     = $this->getRequest()->getParam( 'record-id' );
            $recordMapper = new Application_Model_StaffInvoiceRecordMapper();
            $where        = $recordMapper->getAdapter()->quoteInto( 'id = ?', $recordId );
            $record       = $recordMapper->fetchRow( $where );
            if ( $record->delete() )
            {
                $this->_flash->addMessage( array( 'notice' => 'Record removed' ) );
            }
            else
            {
                $this->_flash->addMessage( array( 'error' => 'Unable to remove record' ) );
            }
            $this->_redirectToInvoice( $record->invoice_id );
        }
        $this->_redirect( '/en/default/staff-invoice/view-invoices' );
    }

    /**
     *
     */
    protected function _redirectToInvoice( $invoiceId )
    {
        if ( $invoiceId )
        {
            $this->_redirect( '/en/default/staff-invoice/view-invoice/id/' . $invoiceId );
        }
        else
        {
            $this->_redirect( '/en/default/staff-invoice/view-invoices' );
        }
    }

}