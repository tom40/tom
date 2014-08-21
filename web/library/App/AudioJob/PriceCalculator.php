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
     * Number of quarter hours
     * @var int
     */
    protected $_quarterHours;

    /**
     * Length in minutes
     * @var int
     */
    protected $_lengthMinutes;

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
        return $this;
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