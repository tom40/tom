<?php

class Application_Model_StaffInvoice_Row extends Zend_Db_Table_Row
{
    /**
     * Comments
     * @var Application_Model_StaffInvoiceComments_Row
     */
    protected $_comments;

    /**
     * User
     * @var array
     */
    protected $_user;

    /**
     *
     */
    public function getStaffName()
    {
        return $this->_data['staff_name'];
    }

    /**
     * Get user
     *
     * @return Zend_Db_Table_Row
     */
    public function getUser()
    {
        if ( null === $this->_user )
        {
            $mapper      = new Application_Model_User();
            $this->_user = $mapper->fetchRow( 'id = ' . $this->_data['user_id'] );
        }
        return $this->_user;
    }

    /**
     * Can delete
     *
     * @param bool $isStaff Is staff
     *
     * @return bool
     */
    public function canDelete( $isStaff )
    {
        if ( $isStaff )
        {
            $canDelete = array(
                Application_Model_StaffInvoiceMapper::STATUS_PENDING,
                Application_Model_StaffInvoiceMapper::STATUS_UN_SUBMITTED
            );

            return ( in_array( $this->_data['status_id'], $canDelete ) );
        }
        return true;
    }

    /**
     * Get pdf file location
     *
     * Returns a location with full path even if the file does not exist
     *
     * @return string
     */
    public function getPdfFileLocation()
    {
        return APPLICATION_PATH . '/../data/staff_invoice/' . $this->name . '.pdf';
    }

    /**
     * Delete PDF
     *
     * @return bool
     */
    public function deletePdf()
    {
        return @unlink( $this->getPdfFileLocation() );
    }

    /**
     * Get file directory
     *
     * @return string
     */
    public function getFileDirectory()
    {
        return dirname( $this->getPdfFileLocation() );
    }

    /**
     * Get file name
     *
     * @return string
     */
    public function getFileName()
    {
        return basename( $this->getPdfFileLocation() );
    }

    /**
     * Has PDF file
     *
     * @return bool
     */
    public function hasPdf()
    {
        return file_exists( $this->getPdfFileLocation() );
    }

    /**
     * View PDF
     *
     * @param object $view Zend View object
     *
     * @return void
     */
    public function viewPdf( $view )
    {
        if ( $this->hasPdf() )
        {
            $this->_servePdf();
        }
        else
        {
            $this->generatePdf( $view, true );
        };
    }

    /**
     * Capture PDF
     *
     * If the PDF does not exist, create it.
     *
     * @param object $view    Zend View object
     * @param bool  $download If true, serve the file to the client
     *
     * @return string|void
     */
    public function generatePdf( $view, $download = true )
    {
        $html2Pdf = new Html2Pdf_MyHtml2Pdf('P', 'A4', 'en');

        $htmlContent = $view->partial(
            '_partials/staff_invoice/invoice.phtml',
            'default',
            array(
                'invoice' => $this
            )
        );

        $html2Pdf->writeHTML( $htmlContent );
        $html2Pdf->Output( $this->getPdfFileLocation(), 'F' );

        if ( $download )
        {
            $this->_servePdf();
        }
        else
        {
            return $this->getPdfFileLocation();
        }
    }

    /**
     * Serve PDF
     *
     * @return void
     */
    protected function _servePdf()
    {
        if ( $this->hasPdf() )
        {
            header( 'Cache-Control: public, must-revalidate, max-age=0' );
            header( 'Pragma: public' );
            header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
            header( 'Last-Modified: '.gmdate('D, d M Y H:i:s' ) . ' GMT' );

            header( 'Content-Type: application/force-download' );
            header( 'Content-Type: application/octet-stream', false );
            header( 'Content-Type: application/download', false );
            header( 'Content-Type: application/pdf', false );

            header( 'Content-Disposition: attachment; filename="' . basename( $this->getPdfFileLocation() ) . '";' );
            header( 'Content-Transfer-Encoding: binary');
            header( 'Content-Length: ' . filesize( $this->getPdfFileLocation() ) );
            echo file_get_contents( $this->getPdfFileLocation() );
            exit();
        }
        return false;
    }

    /**
     * Delete Invoice
     *
     * @return bool
     */
    public function deleteInvoice()
    {
        foreach ( $this->getRecords() as $record )
        {
            $record->delete();
        }

        foreach ( $this->getComments() as $comment )
        {
            $comment->delete();
        }

        if ( $this->hasPdf() )
        {
            $this->deletePdf();
        }
        return $this->delete();
    }

    /**
     * Get URL stub
     *
     * @return string
     */
    public function getUrlStub()
    {
        return '/en/default/staff-invoice/view-invoice/id/' . $this->_data['id'];
    }

    /**
     * Get staff status transitions
     *
     * @return array
     */
    public function getStaffTransitions()
    {
        return Application_Model_StaffInvoiceMapper::getStaffTransitions( $this->_data['status_id'] );
    }

    /**
     * Get admin status transitions
     *
     * @return array
     */
    public function getAdminTransitions()
    {
        return Application_Model_StaffInvoiceMapper::getAdminTransitions( $this->_data['status_id'] );
    }

    /**
     *
     */
    public function getStatusName()
    {
        $mapper = new Application_Model_StaffInvoiceStatusMapper();
        $status = $mapper->fetchRow( 'id = ' . $this->_data['status_id'] );
        return $status['name'];
    }

    /**
     * Set Status
     *
     * @param int $statusId Status ID
     *
     * @return bool
     */
    public function setStatus( $statusId )
    {
        $this->statud_id = $statusId;
        return $this->save();
    }

    /**
     * Get records
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getRecords()
    {
        $mapper = new Application_Model_StaffInvoiceRecordMapper();
        return $mapper->fetchAll( "invoice_id = '" . $this->_data['id'] . "' AND deleted = '0'" );
    }

    /**
     * Total amount due
     *
     * @return float
     */
    public function getTotalAmountDue()
    {
        $amount = 0;
        if ( count( $this->getRecords() ) > 0 )
        {
            foreach ( $this->getRecords() as $record )
            {
                $amount += $record->getAmountDue();
            }
        }
        return $amount;
    }

    /**
     * Comment
     *
     * @param string $comment Comment
     * @param int    $userId  User Id
     *
     * @return bool
     */
    public function comment( $comment, $userId )
    {
        if ( empty( $comment ) || empty( $userId ) || !is_string( $comment ) )
        {
            return false;
        }

        $mapper = new Application_Model_StaffInvoiceCommentsMapper();
        $data   = array(
            'comment'      => $comment,
            'invoice_id'   => $this->_data['id'],
            'created_date' => date( 'Y-m-d H:i:s', time() ),
            'created_by'   => $userId
        );
        return $mapper->insert( $data );
    }

    /**
     * Transition the invoice
     *
     * @param int $statusId
     *
     * @return bool
     */
    public function transition( $statusId )
    {
        $this->status_id = $statusId;

        if ( Application_Model_StaffInvoiceMapper::STATUS_APPROVED === (int) $statusId )
        {
            $this->accepted_date = date( 'Y-m-d H:i:s', time() );
        }
        else
        {
            $this->accepted_date = NULL;
        }

        if ( Application_Model_StaffInvoiceMapper::STATUS_PAID === (int) $statusId )
        {
            $this->paid_date = date( 'Y-m-d H:i:s', time() );
        }
        else
        {
            $this->paid_date = NULL;
        }

        return $this->save();
    }

    /**
     * Get comments
     *
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getComments()
    {
        if ( null === $this->_comments )
        {
            $mapper = new Application_Model_StaffInvoiceCommentsMapper();
            $this->_comments = $mapper->fetchAll( 'invoice_id =' . $this->_data['id'] );
        }
        return $this->_comments;
    }
}