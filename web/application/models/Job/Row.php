<?php

class Application_Model_Job_Row extends Zend_Db_Table_Row
{

    /**
     * Service name
     * @var string
     */
    protected $_serviceName;

    /**
     * Audio Jobs
     * @var Zend_Db_Table_Rowset
     */
    protected $_audioJobs;

    /**
     * Audio Jobs Children
     * @var bool
     */
    protected $_audioJobsChildren;

    /**
     * Status name
     * @var string
     */
    protected $_status;

    /**
     * Client
     * @var Application_Model_Client_Row
     */
    protected $_client;

    /**
     * Primary user
     * @var Application_Model_User_Row
     */
    protected $_primaryUser;

    /**
     * Ad Hoc charges
     * @var Zend_Db_Table_Row
     */
    public $_adHocCharges;

    /**
     * Get default number of speakers name
     *
     * @param string $default Id no name exists use this
     *
     * @return string
     */
    public function getSpeakerNumbersName( $default = '-' )
    {
        if ( null !== $this->speaker_numbers_id )
        {
            if ( $this->isService() )
            {
                $model = new Application_Model_SpeakerNumber();
            }
            else
            {
                $model = new Application_Model_SpeakerNumbersMapper();
            }
            $row = $model->fetchRow( 'id = ' . $this->speaker_numbers_id );
            return $row->name;
        }
        return $default;
    }

    /**
     * Get default turnaround time name
     *
     * @param string $default Id no name exists use this
     *
     * @return string
     */
    public function getTurnaroundTimeName( $default = '-' )
    {
        if ( null !== $this->turnaround_time_id )
        {
            if ( $this->isService() )
            {
                $model = new Application_Model_TurnaroundTime();
            }
            else
            {
                $model = new Application_Model_TurnaroundTimeMapper();
            }
            $row = $model->fetchRow( 'id = ' . $this->speaker_numbers_id );
            return $row->name;
        }
        return $default;
    }

    /**
     * Get default service name
     *
     * @param string $default Id no name exists use this
     *
     * @return string
     */
    public function getServiceName( $default = '-' )
    {
        if ( null == $this->_serviceName )
        {
            if ( null !== $this->service_id )
            {
                $model              = new Application_Model_Service();
                $row                = $model->fetchRow( 'id = ' . $this->service_id );
                $this->_serviceName = $row->name;
            }
            elseif ( null !== $this->transcription_type_id )
            {
                $model              = new Application_Model_TranscriptionTypeMapper();
                $row                = $model->fetchRow( 'id = ' . $this->transcription_type_id );
                $this->_serviceName = $row->name;
            }
            else
            {
                $this->_serviceName = $default;
            }
        }
        return $this->_serviceName;
    }

    /**
     * Get status row
     *
     * @return Zend_Db_Table_Row
     */
    public function getStatus()
    {
        if ( null === $this->_status )
        {
            $model         = new Application_Model_JobStatusMapper();
            $this->_status = $model->fetchRow( 'id = ' . $this->status_id );
        }
        return $this->_status;
    }

    /**
     * Get client row
     *
     * @return Application_Model_Client_Row
     */
    public function getClient()
    {
        if ( null === $this->_client )
        {
            $model         = new Application_Model_ClientMapper();
            $this->_client = $model->fetchRow( 'id = ' . $this->client_id );
        }
        return $this->_client;
    }

    /**
     * Is deleted
     *
     * @return bool
     */
    public function isDeleted()
    {
        return (bool) $this->deleted;
    }

    /**
     * Is completed
     *
     * @return bool
     */
    public function isComplete()
    {
        return (bool) $this->getStatus()->complete;
    }

    /**
     * Is Archived
     *
     * @return bool
     */
    public function isArchived()
    {
        return (bool) $this->archived;
    }

    /**
     * Is Invoiced
     *
     * @return bool
     */
    public function isInvoiced()
    {
        return (bool) $this->getStatus()->invoiced;
    }

    /**
     * Get primary user
     *
     * @return Application_Model_User_Row
     */
    public function getPrimaryUser()
    {
        if ( null ===  $this->_primaryUser )
        {
            $model              = new Application_Model_UserMapper();
            $this->_primaryUser = $model->fetchRow( 'id = ' . $this->primary_user_id );
        }
        return $this->_primaryUser;
    }

    /**
     * Get job discount
     *
     * @return float
     */
    public function getDiscount()
    {
        $discount = 0;
        if ( $this->discount > 0 )
        {
            $discount = $this->discount;
        }
        elseif ( $this->getClient()->discount > 0 )
        {
            $discount = $this->getClient()->discount;
        }
        return $discount;
    }

    /**
     * Get job price
     *
     * @param bool $withDiscount If true use project discount
     *
     * @return float
     */
    public function getPrice( $withDiscount = true )
    {
        $price     = 0;
        $audioJobs = $this->getAudioJobs( false );
        $discount  = 0;
        if ( $withDiscount )
        {
            $discount = $this->getDiscount();
        }

        foreach ( $audioJobs as $audioJob )
        {
            $price += $audioJob->calculatePriceWithChildren( $discount );
        }

        if ( $this->hasAdHocCharges() )
        {
            foreach ( $this->getAdHocCharges() as $adHocCharge )
            {
                $price += $adHocCharge->price;
            }
        }

        return $price;
    }

    /**
     * Get ad hoc charges
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getAdHocCharges()
    {
        if ( null === $this->_adHocCharges )
        {
            $model               = new Application_Model_AdHocMapper();
            $this->_adHocCharges = $model->fetchAll( 'job_id = ' . $this->id );
        }
        return $this->_adHocCharges;
    }

    /**
     * Has ad hoc charges
     *
     * @return bool
     */
    public function hasAdHocCharges()
    {
        return (bool) ( count( $this->getAdHocCharges() ) );
    }

    /**
     * Get Quality name
     *
     * @param string $default Id no name exists use this
     *
     * @return string
     */
    public function getQualityName( $default = '-' )
    {
        if ( null !== $this->audio_quality_id )
        {
            $model = new Application_Model_AudioFileQualityMapper();
            $row   = $model->fetchRow( 'id = ' . $this->audio_quality_id );
            return $row->name;
        }
        return $default;
    }

    /**
     * Is service
     *
     * return bool
     */
    public function isService()
    {
        if ( null !== $this->service_id || null === $this->transcription_type_id )
        {
            return true;
        }
        return false;
    }

    /**
     * Get audio jobs
     *
     * @param bool $children If false only audio jobs that are not children of others
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getAudioJobs( $children = true )
    {
        if ( null === $this->_audioJobs || $this->_audioJobsChildren !== $children )
        {
            $model                    = new Application_Model_AudioJobMapper();
            $this->_audioJobsChildren = $children;
            $select                   = $model->select();

            $select->where( 'deleted IS NULL' );
            $select->where( 'job_id = ?', $this->id );

            if ( false === $children )
            {
                $select->where( 'lead_id IS NULL OR lead_id = 0' );
            }
            $this->_audioJobs = $model->fetchAll( $select );
        }
        return $this->_audioJobs;
    }

}