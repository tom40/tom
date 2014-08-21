<?php
/**
 * Created by JetBrains PhpStorm.
 * User: joemiddleton
 * Date: 20/08/2013
 * Time: 22:29
 * To change this template use File | Settings | File Templates.
 */

class Application_Model_StaffInvoiceRecords_Row extends Zend_Db_Table_Row
{

    /**
     * Audio job
     * @var Zend_Db_Table_Row
     */
    protected $_audioJob;

    /**
     * Project (job)
     * @var Zend_Db_Table_Row
     */
    protected $_project;

    /**
     * Audio job typist
     * @var Zend_Db_Table_Row
     */
    protected $_audioJobTypist;

    /**
     * User
     * @var Zend_Db_Table_Row
     */
    protected $_typistUser;

    /**
     * Service object
     * @var Application_Model_Service_Row
     */
    protected $_service;

    /**
     * Transcription type object
     * @var Zend_Db_Table_Row
     */
    protected $_transcriptionType;

    /**
     * Turnaround time row
     * @var Application_Model_TurnaroundTime_Row|Zend_Db_Table_Row
     */
    protected $_turnaroundTime;

    /**
     * Speaker numbers
     * @var Application_Model_SpeakerNumber_Row
     */
    protected $_speakerNumbers;

    /**
     * Price Modifiers
     * @var
     */
    protected $_priceModifiers;

    /**
     * Get audio job
     *
     * @return Application_Model_AudioJob_Row
     */
    public function getAudioJob()
    {
        if ( null === $this->_audioJob && null !== $this->getAudioJobTypist() )
        {
            $mapper = new Application_Model_AudioJobMapper();
            $this->_audioJob = $mapper->fetchRow( 'id = ' . $this->getAudioJobTypist()->audio_job_id );
        }
        return $this->_audioJob;
    }

    /**
     * Get project
     *
     * @return Zend_Db_Table_Row
     */
    public function getProject()
    {
        if ( null === $this->_project && null !== $this->getAudioJob() )
        {
            $mapper = new Application_Model_JobMapper();
            $this->_project = $mapper->fetchRow( 'id = ' . $this->getAudioJob()->job_id );
        }
        return $this->_project;
    }

    /**
     * Get Record Name
     *
     * @return string
     */
    public function getRecordName()
    {
        if ( null !== $this->getAudioJob() )
        {
            return $this->getAudioJob()->file_name;
        }
        else
        {
            return $this->name;
        }
    }

    /**
     * Get display date
     *
     * @return string
     */
    public function getDisplayDate()
    {
        if ( null !== $this->getAudioJob() )
        {
            return $this->getAudioJob()->created_date;
        }
        else
        {
            return $this->created_date;
        }
    }

    /**
     * Get display ID
     *
     * @return string|int
     */
    public function getDisplayId()
    {
        if ( null !== $this->getAudioJob() )
        {
            return $this->getAudioJob()->job_id;
        }
        else
        {
            return '-';
        }
    }

    /**
     * Get records project status
     *
     * @return string
     */
    public function getProjectStatus()
    {
        if ( null !== $this->getProject() )
        {
            $jobMapper = new Application_Model_JobMapper();
            $status    = $jobMapper->fetchStatus( $this->getProject()->status_id );
            return $status['name'];
        }
        return '-';
    }

    /**
     * Get audio job typist
     *
     * @return Zend_Db_Table_Row
     */
    public function getAudioJobTypist()
    {
        if ( !$this->isAdHoc() && null === $this->_audioJobTypist )
        {
            $mapper                = new Application_Model_AudioJobTypistMapper();
            $this->_audioJobTypist = $mapper->fetchRow( 'id =' . $this->_data['audio_job_typist_id'] );
        }
        return $this->_audioJobTypist;
    }

    /**
     * Get typist
     *
     * @return Zend_Db_Table_Row
     */
    public function getTypistUser()
    {
        if ( !$this->isAdHoc() && null === $this->_typistUser )
        {
            $mapper            = new Application_Model_TypistMapper();
            $this->_typistUser = $mapper->fetchRow( 'user_id =' . $this->getAudioJobTypist()->user_id );
        }
        return $this->_typistUser;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        if ( $this->isAdHoc() )
        {
            return ( empty( $this->_data['name'] )) ? 'Ad-hoc' : $this->_data['name'];
        }
        else
        {
            return $this->getAudioJob()->file_name;
        }
    }

    /**
     * update pay rate
     *
     * @return bool
     */
    public function updatePayRate()
    {
        if ( !$this->isAdHoc() )
        {
            if ( $this->hasService() )
            {
                $rateData = $this->getService()->calculatePrice(
                    $this->speaker_numbers_id,
                    $this->turnaround_time_id,
                    unserialize( $this->price_modifiers )
                );
                $typistGrade          = $this->getTypistUser()->payrate_id;
                $this->pay_per_minute = $rateData['typist_rates'][$typistGrade];
            }
            else
            {
                $payMapper            = new Application_Model_TranscriptionTypistPayrateMapper();
                $this->pay_per_minute = $payMapper->getPayPerMinute($this->transcription_type_id, $this->getTypistUser()->payrate_id );
            }
            return $this->save();
        }
    }

    /**
     * Is accurate
     *
     * @return bool
     */
    public function isInaccurate()
    {
        return (bool) $this->_data['inaccurate'];
    }

    /**
     * Is ad-hoc
     *
     * @return bool
     */
    public function isAdHoc()
    {
        return (bool) $this->_data['ad_hoc'];
    }

    /**
     * Get minutes worked
     *
     * @return string
     */
    public function getMinutesWorked()
    {
        return $this->_data['minutes_worked'];
    }

    /**
     * Return the price per minute, at a percentage if is substandard
     *
     * @deprecated
     *
     * @return int
     */
    public function getPricePerMinute()
    {
        return $this->_data['pay_per_minute'];
    }

    /**
     * Is substandard
     *
     * @return bool
     */
    public function isSubStandard()
    {
        return (bool) $this->_data['sub_standard'];
    }

    /**
     * Get audio job status
     *
     * @return string
     */
    public function getAudioJobStatus()
    {
        $mapper = new Application_Model_AudioJobStatusMapper();
        $status = $mapper->fetchRow( 'id = ' . $this->audio_job_status_id );
        return $status['name'];
    }

    /**
     * Get service or transcription name
     *
     * @return string
     */
    public function getServiceName()
    {
        if ( !$this->isAdHoc() )
        {
            return $this->getCorrectServiceObject()->name;
        }
        return '-';
    }

    /**
     * Uses the new services instead of the old transactions
     *
     * @return bool
     */
    public function hasService()
    {
        return (bool) $this->getService();
    }

    /**
     * Get either the service or legacy transcription type
     *
     * @return Application_Model_Service_Row|Zend_Db_Table_Row
     */
    public function getCorrectServiceObject()
    {
        if ( !$this->isAdHoc() )
        {
            if ( $this->hasService() )
            {
                return $this->getService();
            }
            else
            {
                return $this->getTranscriptionType();
            }
        }
    }

    /**
     * Get service
     *
     * @return Application_Model_Service_Row
     */
    public function getService()
    {
        if ( !empty( $this->service_id ) && null === $this->_service )
        {
            $model          = new Application_Model_Service();
            $this->_service = $model->fetchRow( 'id = ' . $this->service_id );
        }
        return $this->_service;
    }

    /**
     * Get service
     *
     * @return Zend_Db_Table_Row
     */
    public function getTranscriptionType()
    {
        if ( !empty( $this->transcription_type_id ) && null === $this->_transcriptionType )
        {
            $model                    = new Application_Model_TranscriptionTypeMapper();
            $this->_transcriptionType = $model->fetchRow( 'id = ' . $this->transcription_type_id );
        }
        return $this->_transcriptionType;
    }

    /**
     * Get speaker numbers
     *
     * @return void|Application_Model_SpeakerNumbers_Row
     */
    public function getSpeakerNumbers()
    {
        if ( null === $this->_speakerNumbers && !empty( $this->speaker_numbers_id ) )
        {
            $model                 = new Application_Model_SpeakerNumber();
            $this->_speakerNumbers = $model->fetchRow( 'id = ' . $this->speaker_numbers_id );
        }
        return $this->_speakerNumbers;
    }

    /**
     * Get turnaround time
     *
     * @return void|Application_Model_TurnaroundTime_Row
     */
    public function getTurnaroundTime()
    {
        if ( null === $this->_turnaroundTime && !empty( $this->turnaround_time_id ) )
        {
            $model                 = new Application_Model_TurnaroundTime();
            $this->_turnaroundTime = $model->fetchRow( 'id = ' . $this->turnaround_time_id );
        }
        return $this->_turnaroundTime;
    }

    /**
     * Get Additional services string
     *
     * @return string
     */
    public function getAdditionalServicesString()
    {
        $string    = '';
        $modifiers = $this->getAdditionalServices();
        if ( count( $modifiers ) > 0 )
        {
            $stringArray = array();
            foreach ( $modifiers as $modifier )
            {
                $stringArray[] = $modifier->getPriceModifier()->name;
            }
            $string = implode( ', ', $stringArray );
        }
        return $string;
    }

    /**
     * Get Additional services
     *
     * @return string
     */
    public function getAdditionalServices()
    {
        $modifiers = unserialize( $this->price_modifiers );
        if ( null === $this->_priceModifiers && is_array( $modifiers ) && count( $modifiers ) > 0 )
        {
            $model       = new Application_Model_ServiceModifier();
            $select      = $model->select()
                ->where( "id IN('" . implode( "','", $modifiers ) . "')" );

            $this->_priceModifiers = $model->fetchAll( $select );
        }
        return $this->_priceModifiers;
    }

    /**
     * Get amount due
     *
     * @return float
     */
    public function getAmountDue()
    {
        if ( !empty( $this->_data['total'] ) )
        {
            $total = $this->_data['total'];
        }
        else
        {
            $total = $this->pay_per_minute * $this->minutes_worked / 100;
            if ( $this->isSubStandard() )
            {
                $total = Application_Model_TypistPayrateMapper::SUBSTANDARD_PAYRATE * $total;
            }
        }
        return $total;
    }

    /**
     * Mark inaccurate
     *
     * @return bool
     */
    public function markInaccurate()
    {
        $this->inaccurate = 1;
        return $this->save();
    }

    /**
     * Mark inaccurate
     *
     * @return bool
     */
    public function markAccurate()
    {
        $this->inaccurate = 0;
        return $this->save();
    }

    /**
     * Remove record from invoice
     *
     * Invoices removed by this method can't be invoiced again
     *
     * return bool
     */
    public function remove()
    {
        $this->deleted = 1;
        return $this->save();
    }

    /**
     * Set pay per minute from audio job
     *
     * @return return
     */
    public function calculatePayPerMinute()
    {
        $this->pay_per_minute = $this->_getPayPerMinute();
    }

    /**
     * Calculate minutes worked from audio job
     *
     * @return void
     */
    public function calculateMinutesWorked()
    {
        $this->minutes_worked = $this->_getMinutesWorked();
    }

    /**
     * Update record from audio job data
     *
     * @return bool
     */
    public function updateFromAudioJob()
    {
        if ( !$this->isAdHoc() )
        {
            $this->calculateMinutesWorked();
            $this->calculatePayPerMinute();
            $this->transcription_type_id  = $this->getAudioJob()->transcription_type_id;
            $this->service_id             = $this->getAudioJob()->service_id;
            $this->audio_job_status_id    = $this->getAudioJob()->status_id;
            $this->created_date           = $this->getAudioJob()->created_date;
            $this->sub_standard           = $this->getAudioJobTypist()->substandard_payrate;
            $this->replacement            = $this->getAudioJobTypist()->replacement_payrate;
            $this->ad_hoc                 = 0;

            if ( $this->getAudioJob()->hasService() )
            {
                $this->turnaround_time_id = $this->getAudioJob()->turnaround_time_id;
                $this->speaker_numbers_id = $this->getAudioJob()->speaker_numbers_id;
            }

            $this->setPriceModifiers();
            return $this->save();
        }
        return true;
    }

    /**
     * Set price modifier column
     *
     * @return void
     */
    public function setPriceModifiers()
    {
        if ( $this->getAudioJob()->hasService() )
        {
            $modifiers = $this->getAudioJob()->getPriceModifiers();
            if ( count( $modifiers ) > 0 )
            {
                $modifierArray = array();
                foreach ( $modifiers as $modifier )
                {
                    $modifierArray[] = $modifier->getServicePriceModifier()->id;
                }
                $this->price_modifiers = serialize( $modifierArray );
            }
        }
    }

    /**
     * Get minutes worked for generated invoices
     *
     * @return int
     */
    protected function _getMinutesWorked()
    {
        if ( null !== $this->getAudioJobTypist()->minutes_start )
        {
            $minutes = $this->getAudioJobTypist()->minutes_end - $this->getAudioJobTypist()->minutes_start;
        }
        else
        {
            $minutes = Application_Model_AudioJobMapper::getMinutesFromFileTime( $this->getAudioJob()->length_seconds );
        }
        return $minutes;
    }

    /**
     * Return the price per minute, at a percentage if is substandard
     *
     * @return int
     */
    protected function _getPayPerMinute()
    {
        if( (bool) $this->getAudioJobTypist()->substandard_payrate )
        {
            $price = Application_Model_TypistPayrateMapper::SUBSTANDARD_PAYRATE * $this->getAudioJobTypist()->pay_per_minute;
        }
        elseif ( (bool) $this->getAudioJobTypist()->replacement_payrate )
        {
            $price = Application_Model_TypistPayrateMapper::REPLACEMENT_PAYRATE * $this->getAudioJobTypist()->pay_per_minute;
        }
        else
        {
            $price = $this->getAudioJobTypist()->pay_per_minute;
        }
        return $price;
    }
}