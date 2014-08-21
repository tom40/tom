<?php
/**
 * Created by JetBrains PhpStorm.
 * User: joemiddleton
 * Date: 20/08/2013
 * Time: 16:48
 * To change this template use File | Settings | File Templates.
 */

class Application_Model_StaffInvoiceMapper extends App_Db_Table
{

    const STATUS_UN_SUBMITTED = 1;
    const STATUS_PENDING      = 2;
    const STATUS_APPROVED     = 3;
    const STATUS_REJECTED     = 4;
    const STATUS_PAID         = 5;
    const STATUS_RETURNED     = 6;

    /**
     * Staff transitions
     * @var array
     */
    protected static $_staffTransitions = array(
        self::STATUS_UN_SUBMITTED => array(
            'Approve' => self::STATUS_APPROVED,
            'Reject'  => self::STATUS_REJECTED
        ),
        self::STATUS_PENDING      => array(
            'Approve' => self::STATUS_APPROVED,
            'Reject'  => self::STATUS_REJECTED
        ),
        self::STATUS_RETURNED     => array(
            'Approve' => self::STATUS_APPROVED,
            'Reject'  => self::STATUS_REJECTED
        ),
        self::STATUS_REJECTED     => array( 'Approve' => self::STATUS_APPROVED ),
        self::STATUS_APPROVED     => array( 'Reject' => self::STATUS_REJECTED )
    );

    protected static $_adminTransitions = array(
        self::STATUS_APPROVED => array(
            'Paid'   => self::STATUS_PAID,
            'Accept' => self::STATUS_PENDING
        ),
        self::STATUS_REJECTED => array(
            'Return' => self::STATUS_RETURNED,
            'Accept' => self::STATUS_PENDING
        ),
        self::STATUS_PENDING      => array(
            'Paid'   => self::STATUS_PAID
        ),
    );

    /**
     * Table name
     * @var string
     */
    protected $_name = 'staff_invoice';

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_StaffInvoice_Row';

    /**
     * Audio job mapper
     * @var Application_Model_AudioJobMapper
     */
    protected $_audioJobMapper;

    /**
     * Typist job mapper
     * @var Application_Model_AudioJobTypistMapper
     */
    protected $_audioJobTypistMapper;

    /**
     * Record mapper
     * @var Application_Model_StaffInvoiceRecordMapper
     */
    protected $_recordMapper;

    /**
     * Get audio job mapper object
     *
     * @return Application_Model_AudioJobMapper
     */
    protected function _getAudioJobMapper()
    {
        if ( null === $this->_audioJobMapper)
        {
            $this->_audioJobMapper = new Application_Model_AudioJobMapper();
        }
        return $this->_audioJobMapper;
    }

    /**
     * Get audio job typist mapper object
     *
     * @return Application_Model_AudioJobTypistMapper
     */
    protected function _getAudioJobTypistMapper()
    {
        if ( null === $this->_audioJobTypistMapper)
        {
            $this->_audioJobTypistMapper = new Application_Model_AudioJobTypistMapper();
        }
        return $this->_audioJobTypistMapper;
    }

    /**
     * Get audio record mapper object
     *
     * @return Application_Model_StaffInvoiceRecordMapper
     */
    protected function _getRecordMapper()
    {
        if ( null === $this->_recordMapper)
        {
            $this->_recordMapper = new Application_Model_StaffInvoiceRecordMapper();
        }
        return $this->_recordMapper;
    }

    /**
     * Get invoice statuses for staff users
     *
     * @param int $statusId Status ID
     *
     * @return array
     */
    public static function getStaffTransitions( $statusId )
    {
        if ( isset( self::$_staffTransitions[$statusId] ) )
        {
            return self::$_staffTransitions[$statusId];
        }
        return array();
    }

    /**
     * Get invoice statuses for Admin users
     *
     * @param int $statusId Status ID
     *
     * @return array
     */
    public static function getAdminTransitions( $statusId )
    {
        if ( isset( self::$_adminTransitions[$statusId] ) )
        {
            return self::$_adminTransitions[$statusId];
        }
        return array();
    }

    /**
     * Get invoices for user with status
     *
     * @param int $userId   User ID
     * @param int $statusId Status ID
     *
     * @return Zend_Db_Table_Row
     */
    public function getInvoices( $userId = null, $statusId = null )
    {
        $where = $this->select();

        if ( null != $userId )
        {
            $where->where( 'user_id = ?', $userId );
        }

        if ( null != $statusId )
        {
            $where->where( 'status_id = ?', $statusId );
        }
        return $this->fetchAll( $where );
    }

    /**
     * Get admin transitions for status
     *
     * @param int $statusId Status ID
     *
     * @return array
     */
    public function getAdminTransitionsForStatus( $statusId )
    {
        $row = $this->fetchRow( 'status_id = ' . $statusId );

        if ( $row )

        return $row->getAdminTransitions();
    }

    /**
     * Get Invoice
     *
     * @param int $invoiceId Invoice ID
     *
     * @return Application_Model_StaffInvoice_Row
     */
    public function getInvoice( $invoiceId )
    {
        return $this->fetchRow( 'id = ' . $invoiceId );
    }

    /**
     * Generate invoice
     *
     * @param int   $userId User ID
     * @param array $data   Date info
     *
     * @return array
     */
    public function generateInvoice( $userId, $data )
    {
        $typistMapper = new Application_Model_AudioJobTypistMapper();
        $typistDb     = $typistMapper->getAdapter();

        $startDate = date( 'Y-m-d H:i:s', strtotime( $data['start_date'] ) );
        $endDate   = date( 'Y-m-d', strtotime( $data['end_date'] ) ) . ' 23:59:59';
        $now       = date( 'Y-m-d H:i:s', time() );

        $userMapper = new Application_Model_User();
        $user       = $userMapper->fetchRow( 'id = ' . $userId );

        $invoiceData = array(
            'user_id'      => $userId,
            'staff_name'   => $user['name'],
            'status_id'    => 1,
            'date_start'   => $startDate,
            'date_end'     => $endDate,
            'created_date' => $now,
            'updated_date' => $now
        );

        if ( !isset( $data['empty_invoice'] ) )
        {
            $typistSelect = $typistDb->select()
                ->from( array( 'ajt' => 'audio_jobs_typists' ), array( 'ajt.id' ) )
                ->joinLeft( array( 'sir' => 'staff_invoice_records' ), 'sir.audio_job_typist_id = ajt.id', array() )
                ->joinLeft( array( 'aj' => 'audio_jobs' ), 'ajt.audio_job_id = aj.id', array() )
                ->where( 'sir.id IS NULL' )
                ->where( 'aj.archived = ?', '0' )
                ->where( 'ajt.created_date >= ?', $startDate )
                ->where( 'ajt.created_date <= ?', $endDate )
                ->where( 'ajt.user_id = ?', $userId )
                ->where( "(ajt.current = '1' OR ajt.substandard_payrate = '1')" )
                ->where( "aj.deleted IS NULL" );

            $results = $typistDb->fetchAll( $typistSelect );

            if ( count( $results ) > 0 )
            {
                $invoiceId = $this->insert( $invoiceData );

                foreach ( $results as $typist )
                {
                    $this->_createRecord( $invoiceId, $typist );
                }

                $invoice = $this->getInvoice( $invoiceId );
                $this->_generateInvoiceName( $invoice );
                return $invoice;
            }
        }
        else
        {
            $invoiceId = $this->insert( $invoiceData );
            $invoice   = $this->getInvoice( $invoiceId );
            $this->_generateInvoiceName( $invoice );
            return $invoice;
        }
        return false;
    }

    /**
     *
     */
    protected function _createRecord( $invoiceId, $typist )
    {
        $newRecord                      = $this->_getRecordMapper()->createRow();
        $newRecord->invoice_id          = $invoiceId;
        $newRecord->audio_job_typist_id = $typist['id'];

        return $newRecord->updateFromAudioJob();
    }

    /**
     * Generate name
     *
     * @param App_Model_StaffInvoice_Row $invoice Invoice object
     *
     * @return string
     */
    protected function _generateInvoiceName( $invoice )
    {
        $userMapper = new Application_Model_User();
        $user       = $userMapper->fetchRow( 'id = ' . $invoice->user_id );

        $name = explode( ' ', strtoupper( $user->name ) );

        $namePart = '';

        foreach( $name as $part )
        {
            $namePart .= substr( $part, 0, 2 );
        }

        $namePart      = substr( $namePart, 0, 4 );
        $userIdPart    = str_pad( $invoice->user_id, 4, 0, STR_PAD_LEFT );
        $invoiceIdPart = str_pad( $invoice->id, 4, 0, STR_PAD_LEFT );

        $invoice->name = $namePart . '-' . $userIdPart . '-' . $invoiceIdPart;
        return $invoice->save();
    }

}