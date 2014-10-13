<?php
/**
 * Calculate audio job price.
 *
 * This has been extracted to it's own class so quotes can use the same business logic
 *
 * Class App_AudioJob_PriceCalculator
 */
class App_AudioJob_PriceCalculator
{

    /**
     * Total price
     * @var float
     */
    protected $_price = 0;

    /**
     * Rate
     *
     * The rate is either supplied or te be calculated by self::calculateRate()
     *
     * @var float
     */
    protected $_rate;

    /**
     * Number of quarter hours calculated with rules that round up with some allowances 
     * @var int
     */
    protected $_quarterHours;

    /**
     * Length in minutes rounded up to the nearest whole minute
     * @var int
     */
    protected $_lengthMinutes;
    
    /**
     * Length in seconds based on the rounded up number of minutes
     * @var int
     */
    protected $_lengthSeconds;    

    /**
     * Interval modifier rate
     * @var float
     */
    protected $_intervalRateModifier;

    /**
     * Additional services price
     * @var float
     */
    protected $_additionalServicesTotal;

    /**
     * Audio job discount
     * @var float
     */
    protected $_audioJobDiscount;

    /**
     * Reset price
     *
     * @return void
     */
    public function resetPrice()
    {
        $this->_price = 0;
    }

    /**
     * Set rate
     *
     * @param float $rate
     *
     * @return App_AudioJob_PriceCalculator
     */
    public function setRate( $rate )
    {
        $this->_rate = $rate;
        return $this;
    }

    /**
     * Set rate modifier
     *
     * @param float $intervalRateModifier
     *
     * @return App_AudioJob_PriceCalculator
     */
    public function setIntervalRateModifier( $intervalRateModifier )
    {
        $this->_intervalRateModifier = $intervalRateModifier;
        return $this;
    }

    /**
     * Set length in minutes
     *
     * @param float $lengthMinutes
     *
     * @return App_AudioJob_PriceCalculator
     */
    public function setLengthMinutes( $lengthMinutes )
    {
        $this->_lengthMinutes = $lengthMinutes;
        $this->_lengthSeconds = $lengthMinutes * 60;
        return $this;
    }

    /**
     * Function to call to set the length in seconds and have it converted in
     * the standard way into minutes. This must be the total length for the 
     * audio file and any children.
     */
    public function setLengthSeconds($lengthSeconds)
    {
        $minutes = 0;
        
        // If we receive seconds then round up to the next minute. This uses a
        // simpler method to
        // achieve this than is used in the AudioRow object
        if (!empty($lengthSeconds))
        {
            $minutes = ceil($lengthSeconds / 60);
        }
        
        $this->_lengthMinutes = $minutes;
        $this->_lengthSeconds = $minutes * 60;
    }
    
    /**
     * Set quarter hours
     *
     * @param float $quarterHours
     *
     * @return App_AudioJob_PriceCalculator
     */
    public function setQuarterHours( $quarterHours )
    {
        $this->_quarterHours = $quarterHours;
        return $this;
    }

    /**
     * Set audio job discount
     *
     * @param float $audioJobDiscount
     *
     * @return App_AudioJob_PriceCalculator
     */
    public function setAudioJobDiscount( $audioJobDiscount )
    {
        $this->_audioJobDiscount = $audioJobDiscount;
        return $this;
    }
    
    /**
     * Set additional services total
     *
     * @param float $additionalServicesTotal
     *
     * @return App_AudioJob_PriceCalculator
     */
    public function setAdditionalServicesTotal($additionalServicesTotal)
    {
    	$this->_additionalServicesTotal = $additionalServicesTotal;
    	return $this;
    }

    /**
     * Set additional services total price.
     * This can only be called if the length in seconds or
     * minutes has already been set.
     *
     * @param float $additionalServicesPrice            
     *
     * @return App_AudioJob_PriceCalculator
     */
    public function setAdditionalServicesPrice($additionalServicesPrice)
    {
        $return = false;
        
        if (isset($this->_lengthSeconds))
        {
            $this->_additionalServicesTotal = ceil(
                $this->_lengthSeconds / (15 * 60)) * $additionalServicesPrice;
        }
        
        return $this;
    }
        
    /**
     * Get the number of seconds after they have been rounded to the nearest minute
     *
     * @return int
     */
    public function getLengthSeconds()
    {
    	return $this->_lengthSeconds;
    }
        
    /**
     * Return the number of quarter hours used in the calculation 
     *
     * @return int
     */
    public function getQuarterHours()
    {
    	return $this->_quarterHours;
    }
    
    /**
     * Calculate rate
     *
     * @param int   $serviceId        Service ID
     * @param int   $speakerNumbersId Speaker numbers ID
     * @param int   $turnaroundTimeId Turnaround time ID
     * @param array $priceModifiers   Array of price modifiers
     *
     * @return self
     */
    public function calculateRate( $serviceId, $speakerNumbersId, $turnaroundTimeId, $priceModifiers )
    {
        $model   = new Application_Model_Service();
        $service = $model->fetchRow( 'id = ' . $serviceId );

        $rateData = $service->calculatePrice(
             $speakerNumbersId,
             $turnaroundTimeId,
             $priceModifiers
        );

        $this->setRate( $rateData['rate'] );
        $this->setIntervalRateModifier( $rateData['interval_rate_modifier'] );

        return $this;
    }

    /**
     * Calculate audio job price
     *
     * @param bool $reset If true, reset price to 0
     *
     * @return float
     */
    public function calculatePrice( $reset = true )
    {
        $this->_testRequiredProperties();

        if ( $reset )
        {
            $this->resetPrice();
        }

        if ( null !== $this->_lengthMinutes )
        {
            $this->_calculateQuarterHours();
        }

        $this->_price += $this->_quarterHours * $this->_rate;

        if ( !empty( $this->_audioJobDiscount ) )
        {
            $this->_price = $this->_price - ( ( $this->_price / 100 ) * $this->_audioJobDiscount );
        }

        if ( !empty( $this->_additionalServicesTotal ) )
        {
            $this->_price += $this->_additionalServicesTotal;
        }

        return $this->_price;
    }

    /**
     * Calculate quarter hours from length seconds
     *
     * @return void
     */
    protected function _calculateQuarterHours()
    {
        $noQuarterHours   = floor( $this->_lengthMinutes / 15);
        $remainingMinutes = floor( $this->_lengthMinutes - ( $noQuarterHours * 15 ) );

        if ( 0 == $noQuarterHours || $remainingMinutes > Application_Model_AudioJob_Row::GRACE_PERIOD )
        {
            $noQuarterHours += 1;
        }
        $this->_quarterHours = $noQuarterHours;
    }

    /**
     * Test properties
     *
     * @throws Exception if all rules can not be met
     *
     * @return void
     */
    protected function _testRequiredProperties()
    {
        if ( null === $this->_rate )
        {
            throw new Exception( 'Base rate must be set' );
        }

        if ( null === $this->_lengthMinutes && null === $this->_quarterHours )
        {
            throw new Exception( 'A time duration must be set' );
        }
    }

}