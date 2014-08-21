<?php

/**
 * Class Application_Model_AudioJob_Row
 *
 * @property float $length_seconds
 * @property float $poor_audio
 */
class Application_Model_AudioJob_Row extends Zend_Db_Table_Row
{

    /**
     * Number of minutes above the interval of 15 which are not charged
     */
    const GRACE_PERIOD = 2;

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
     * Child audio jobs
     * @var Zend_Db_Table_Rowset
     */
    protected $_children;

    /**
     * Price modifiers
     * @var Zend_Db_Table_Rowset
     */
    protected $_priceModifiers;

    /**
     * Parent project
     * @var Application_Model_Job_Row
     */
    protected $_project;

    /**
     * Price calculator object
     * @var App_AudioJob_PriceCalculator
     */
    protected $_priceCalculator;

    /**
     * Turnaround time row
     * @var Application_Model_TurnaroundTime_Row|Zend_Db_Table_Row
     */
    protected $_turnaroundTime;

    /**
     * Speaker Numbers
     * @var Application_Model_ServiceSpeakerNumber_Row|Zend_Db_Table_Row
     */
    protected $_speakerNumber;

    /**
     * Save method
     *
     * @throws Exception to prevent an audio job being created using a row class.  There hasn't been time
     *                   to extract and refactor the work done in the table class to make this successful.
     *                   The Row class can only be used to update am audio job and only in addition to the update
     *                   work done in the table class.
     *
     * @return mixed
     */
    public function save()
    {
        if (empty($this->_cleanData))
        {
            throw Exception( 'Can not create Audiojob using the row class' );
        }
        return parent::save();
    }

    /**
     * Get price calculator object
     *
     * @param bool $reset Reset the object
     *
     * @return App_AudioJob_PriceCalculator
     */
    protected function _getPriceCalulcator( $reset = false )
    {
        if ( $reset || null === $this->_priceCalculator )
        {
            $this->_priceCalculator = new App_AudioJob_PriceCalculator();
        }
        return $this->_priceCalculator;
    }

    /**
     * Get project
     *
     * @return Application_Model_Job_Row
     */
    public function getJob()
    {
        if ( null === $this->_project )
        {
            $model          = new Application_Model_JobMapper();
            $this->_project = $model->fetchRow( 'id = ' . $this->job_id );
        }
        return $this->_project;
    }

    /**
     * Is this a child of another audio job
     *
     * @return bool
     */
    public function isChild()
    {
        return !( empty( $this->lead_id ) );
    }

    /**
     * Get children
     *
     * @return Zend_Db_Table_Row
     */
    public function getChildren()
    {
        if ( null === $this->_children )
        {
            $model           = new Application_Model_AudioJobMapper();
            $this->_children = $model->fetchAll( "lead_id = '" . $this->id ."'" );
        }
        return $this->_children;
    }

    /**
     * Has children
     *
     * @return bool
     */
    public function hasChildren()
    {
        return ( bool ) count( $this->getChildren() );
    }

    /**
     * Get child length in seconds
     *
     * @return float
     */
    public function getChildrenLengthSeconds()
    {
        $total = 0;
        if ( $this->hasChildren() )
        {
            $children = $this->getChildren();
            foreach ( $children as $child )
            {
                $total += $child->getTotalLengthSeconds();
            }
        }
        return $total;
    }

    /**
     * Get length in seconds
     *
     * If a lead job, includes children length seconds
     *
     * @return float
     */
    public function getTotalLengthSeconds()
    {
        if ( !empty( $this->length_seconds ) )
        {
            $lengthSeconds = ( $this->length_seconds + $this->getChildrenLengthSeconds() );
            return $lengthSeconds + ( (int ) ( bool ) ( $lengthSeconds % 60 ) ) * ( 60 - ( $lengthSeconds % 60 ) );
        }
        return 0;
    }

    /**
     * Get minutes from seconds
     *
     * @return float
     */
    public function getMinutesFromSeconds()
    {
        return round( ( $this->getTotalLengthSeconds() / 60 ) );
    }

    /**
     * Get the total of the interval rate modifier
     *
     * @return float
     */
    public function getIntervalRateModifierTotal()
    {
        $total = 0;
        if ( $this->interval_rate_modifier )
        {
            $total = $this->getQuarterHours() * ( $this->interval_rate_modifier / 100 );
        }
        return $total;
    }

    /**
     * Get quarter hours
     *
     * @return float
     */
    public function getQuarterHours()
    {
        $noQuarterHours   = floor( $this->getMinutesFromSeconds() / 15);
        $remainingMinutes = floor( $this->getMinutesFromSeconds() - ( $noQuarterHours * 15 ) );

        if ( $remainingMinutes > self::GRACE_PERIOD )
        {
            $noQuarterHours += 1;
        }
        return $noQuarterHours;
    }

    /**
     * Get hours
     *
     * @return float
     */
    public function getHours()
    {
        return $this->getQuarterHours() / 4;
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
     * Get cost per minute
     *
     * @return float
     */
    public function getPricePerMinute()
    {
        return ( $this->rate / 15 );
    }

    /**
     * Get either the service or legacy transcription type
     *
     * @return Application_Model_Service_Row|Zend_Db_Table_Row
     */
    public function getCorrectServiceObject()
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
     * Get turnaround time
     *
     * @return Zend_Db_Table_Row
     */
    public function getTurnaroundTime()
    {
        if ( null === $this->_turnaroundTime )
        {
            if ( $this->hasService() )
            {
                $model = new Application_Model_TurnaroundTime();
            }
            else
            {
                $model = new Application_Model_TurnaroundTimeMapper();
            }
            $this->_turnaroundTime = $model->fetchRow( 'id = ' . $this->turnaround_time_id );
        }
        return $this->_turnaroundTime;
    }

    /**
     * Get speaker numbers
     *
     * @return Zend_Db_Table_Row
     */
    public function getSpeakerNumber()
    {
        if ( null === $this->_speakerNumber )
        {
            if ( $this->hasService() )
            {
                $model = new Application_Model_SpeakerNumber();

            }
            else
            {
                $model = new Application_Model_SpeakerNumbersMapper();
            }
            $this->_speakerNumber = $model->fetchRow( 'id = ' . $this->speaker_numbers_id );
        }
        return $this->_speakerNumber;
    }

    /**
     * Get price modifiers
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getPriceModifiers()
    {
        if ( !empty( $this->service_id ) && null === $this->_priceModifiers )
        {
            $model                 = new Application_Model_AudioJobsPriceModifiers();
            $this->_priceModifiers = $model->fetchAll( 'audio_job_id = ' . $this->id );
        }
        return $this->_priceModifiers;
    }

    /**
     * Get price modifier string
     *
     * @return string
     */
    public function getPriceModifierString()
    {
        $string    = '';
        $modifiers = $this->getPriceModifiers();
        if ( count( $modifiers ) > 0 )
        {
            $stringArray = array();
            foreach ( $modifiers as $modifier )
            {
                $stringArray[] = $modifier->getServicePriceModifier()->getPriceModifier()->name;
            }
            $string = implode( ', ', $stringArray );
        }
        return $string;
    }

    /**
     * Get poor audio percentage.
     *
     * Poor audio is stored as a float, e.g. 40% = 1.4 for easier calculations, this method returns
     * the % format
     *
     * return int
     */
    public function getPoorAudioPercentage()
    {
        if ( $this->poor_audio > 0 )
        {
            return ( $this->poor_audio - 1 ) * 100;
        }
        return 0;
    }

    /**
     * Calculate due data
     *
     * @return mixed
     */
    public function updateDueDate()
    {
        if ( empty( $this->manual_client_due_date ) )
        {
            $time                  = strtotime( $this->created_date );
            $model                 = new Application_Model_TurnaroundTimeMapper();
            $this->client_due_date = $model->calculateDueDate( $this->turnaround_time_id, $time, $this->hasService() );
        }
    }

    /**
     * Update service price modifiers
     *
     * @param array $modifiers Array of service price modifiers
     *
     * @return void
     */
    public function updatePriceModifiers( $modifiers )
    {
        $model = new Application_Model_AudioJobsPriceModifiers();
        $model->delete( 'audio_job_id = ' . $this->id );

        if ( count( $modifiers ) > 0 )
        {
            foreach ( $modifiers as $modifierId )
            {
                $newRow                            = $model->createRow();
                $newRow->audio_job_id              = $this->id;
                $newRow->service_price_modifier_id = $modifierId;
                $newRow->save();
            }
        }
    }

    /**
     * Has Modifier
     *
     * @param int $modifierId Modifier ID
     *
     * @return bool
     */
    public function hasPriceModifier( $modifierId )
    {
        $modifiers = $this->getPriceModifiers();
        if ( count( $modifiers ) > 0 )
        {
            foreach ( $modifiers as $modifier )
            {
                if ( $modifierId == $modifier->service_price_modifier_id )
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Calculate price with children
     *
     * @param bool $jobDiscount If true factor in Project discount
     *
     * @return float
     */
    public function calculatePriceWithChildren( $jobDiscount = false )
    {
        return $this->calculatePrice( $jobDiscount ) + $this->calculateChildrenPrice( $jobDiscount );
    }

    /**
     * Calculate the price of this audio job
     *
     * @param bool $jobDiscount      If true factor in Project discount
     * @param bool $audioJobDiscount If true factor in audio job discount
     *
     * @return float
     */
    public function calculatePrice( $jobDiscount = false, $audioJobDiscount = true )
    {
        $price = 0;

        if ( empty( $this->lead_id ) )
        {
            $additionalServiceModel = new Application_Model_AdditionalServicesMapper();
            $additionalServices     = $additionalServiceModel->getTotalPriceByAudioJob( $this->id, $this->getTotalLengthSeconds() );

            if ( empty( $this->rate ) )
            {
                $this->updateRates();
            }

            $this->_getPriceCalulcator()
                ->setRate( $this->rate )
                ->setLengthMinutes( $this->getMinutesFromSeconds() )
                ->setIntervalRateModifier( $additionalServices );

            if ( $audioJobDiscount )
            {
                $this->_getPriceCalulcator()->setAudioJobDiscount( $this->audio_job_discount );
            }

            $price = $this->_getPriceCalulcator()->calculatePrice();

            if ( $this->poor_audio > 0 )
            {
                $price = $price * $this->poor_audio;
            }

            if ( $jobDiscount )
            {
                if ( $this->getJob()->getDiscount() > 0 )
                {
                    $price = $price - ( ( $price / 100 ) * $jobDiscount );
                }
            }
        }
        return $price;
    }

    /**
     * Get child length in seconds
     *
     * @param bool $jobDiscount If true factor in Project discount
     *
     * @return float
     */
    public function calculateChildrenPrice( $jobDiscount = false )
    {
        $total = 0;
        if ( $this->hasChildren() )
        {
            $children = $this->getChildren();
            foreach ( $children as $child )
            {
                $total += $child->calculatePrice( $jobDiscount );
            }
        }
        return $total;
    }


    /**
     * Update price
     *
     * @return mixed
     */
    public function updateRates()
    {
        if ( !empty( $this->service_id ) )
        {
            $this->_updatePriceService();
        }
        else
        {
            $this->_updatePriceTranscriptionType();
        }
        return $this->save();
    }

    /**
     * Update typist rates only
     *
     * @return mixed
     */
    public function updateTypistRates()
    {
        if ( !empty( $this->service_id ) )
        {
            $typistRates = $this->getTypistRates();
            $this->_updateTypistRateService( $typistRates );
        }
        else
        {
            $this->_updateTypistRateTranscriptionType();
        }
        return $this->save();
    }

    /**
     * Get typist pay details
     *
     * @return array
     */
    public function getTypistRates()
    {
        $rateData = $this->getService()->calculatePrice(
            $this->speaker_numbers_id,
            $this->turnaround_time_id,
            $this->getPriceModifiers()
        );
        return $rateData['typist_rates'];
    }

    /**
     * Toggle poor quality
     *
     * @param int $poorAudio Poor audio value
     *
     * @return mixed
     */
    public function togglePoorAudio( $poorAudio = 0 )
    {
        if ( !is_numeric( $poorAudio ) || ( $poorAudio > 100 || $poorAudio < 0 ) )
        {
            return false;
        }
        if ( $poorAudio > 0 )
        {
            $this->poor_audio = 1 + ( $poorAudio / 100 );
        }
        else
        {
            $this->poor_audio = 0;
        }
        return $this->updateTypistRates();
    }

    /**
     * Update price form service price
     *
     * @return void
     */
    protected function _updatePriceService()
    {
        $rateData = $this->getService()->calculatePrice(
            $this->speaker_numbers_id,
            $this->turnaround_time_id,
            $this->getPriceModifiers()
        );

        $this->rate                   = $rateData['rate'];
        $this->interval_rate_modifier = $rateData['interval_rate_modifier'];
        $this->_updateTypistRateService( $rateData['typist_rates'] );
    }

    /**
     * Update price from old style transcription type
     *
     * @return void
     */
    protected function _updatePriceTranscriptionType()
    {
        $priceMapper  = new Application_Model_TranscriptionPriceMapper();
        $this->rate   = $priceMapper->getPrice( $this->transcription_type_id, $this->turnaround_time_id );
        $this->_updateTypistRateTranscriptionType();
    }

    /**
     * update typist rate from service
     *
     * @param array $typistRates Pre calculated typist pay rates
     *
     * @return void
     */
    protected function _updateTypistRateService( $typistRates )
    {
        $audioJobTypistMapper = new Application_Model_AudioJobTypistMapper;
        $audioJobTypistMapper->updatePayPerMinute( $this->id, $typistRates, $this->poor_audio );
    }

    /**
     * update typist pay rate from transcription type
     *
     * @return void
     */
    protected function _updateTypistRateTranscriptionType()
    {
        if ( ( $this->_cleanData['transcription_type_id'] !== $this->transcription_type_id ) || ( $this->_cleanData['poor_audio'] !== $this->poor_audio ) )
        {
            $audioJobTypistMapper = new Application_Model_AudioJobTypistMapper;
            $audioJobTypistMapper->updatePayPerMinute( $this->id, null, $this->poor_audio );
        }
    }
}