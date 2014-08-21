<?php

/**
 * @property int   $id
 * @property float $base_rate
 * @property float $typist_grade_1
 * @property float $typist_grade_2
 * @property float $typist_grade_3
 * @property float $typist_grade_4
 * @property float $typist_grade_5
 */
class Application_Model_Service_Row extends Zend_Db_Table_Row
{

    /**
     * Turnaround times cache
     * @var array
     */
    protected $_turnaroundTimes;

    /**
     * Service speaker numbers
     * @var array
     */
    protected $_speakerNumbers;

    /**
     * Modifiers
     * @var array
     */
    protected $_priceModifiers;

    /**
     * Can delete
     * @var bool
     */
    protected $_canDelete;

    /**
     * Pre delete, remove all relationships
     *
     * @return void
     */
    protected function _delete()
    {
        $this->_deleteRows( $this->getServiceTurnaroundTimes() );
        $this->_deleteRows( $this->getServicePriceModifiers() );
        $this->_deleteRows( $this->getServiceSpeakerNumbers() );

        $clientServiceModel = new Application_Model_ClientService();
        $clientServiceModel->delete( 'service_id = ' . $this->id );

        $serviceGroupModel = new Application_Model_ServiceGroupService();
        $serviceGroupModel->delete( 'service_id = ' . $this->id );
    }

    /**
     * Delete rows
     *
     * @param Zend_Db_Table_Rowset $rows
     *
     * @return void
     */
    protected function _deleteRows( $rows )
    {
        if ( count( $rows ) )
        {
            foreach ( $rows as $row )
            {
                $row->delete();
            }
        }
    }

    /**
     * Update row from array
     *
     * @param array $data Data array
     *
     * @return bool
     */
    public function update( $data )
    {
        foreach ( $data as $key => $value )
        {
            $this->{$key} = $value;
        }
        return $this->save();
    }

    /**
     * Can this service be deleted
     *
     * @return bool
     */
    public function canDelete()
    {
        if ( null === $this->_canDelete )
        {
            $model   = new Application_Model_AudioJobMapper();
            $results = $model->fetchAll( 'service_id = ' . $this->id );
            $this->_canDelete = !(bool) count( $results );
        }
        return $this->_canDelete;
    }

    /**
     * Get associated turnaround times
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getServiceTurnaroundTimes()
    {
        if ( null === $this->_turnaroundTimes )
        {
            $model                  = new Application_Model_ServiceTurnaround();
            $this->_turnaroundTimes = $model->fetchAll( "service_id = '" . $this->id . "'" );
        }
        return $this->_turnaroundTimes;
    }

    /**
     * Get service turnaround times for drop down
     *
     * @return array
     */
    public function getServiceTurnaroundTimesDropDown()
    {
        $serviceTurnaroundTimes = $this->getServiceTurnaroundTimes();

        if ( count( $serviceTurnaroundTimes ) > 0 )
        {
            foreach ( $serviceTurnaroundTimes as $turnAroundTime )
            {
                $turnAroundTimes[ $turnAroundTime->getTurnaroundTime()->id ] = $turnAroundTime->getTurnaroundTime()->name;
            }
            return $turnAroundTimes;
        }
        return array();
    }

    /**
     * Get associated speaker numbers
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getServiceSpeakerNumbers()
    {
        if ( null === $this->_speakerNumbers )
        {
            $model                 = new Application_Model_ServiceSpeakerNumber();
            $this->_speakerNumbers = $model->fetchAll( "service_id = '" . $this->id . "'" );
        }
        return $this->_speakerNumbers;
    }

    /**
     * Get service speaker numbers for a select drop down
     *
     * @return array
     */
    public function getServiceSpeakerNumbersDropDown()
    {
        $serviceSpeakerNumbers = $this->getServiceSpeakerNumbers();

        if ( count( $serviceSpeakerNumbers ) > 0 )
        {
            foreach ( $serviceSpeakerNumbers  as $speakerNumber )
            {
                $speakerNumbers[ $speakerNumber->getSpeakerNumber()->id ] = $speakerNumber->getSpeakerNumber()->name;
            }
            return $speakerNumbers;
        }
        return array();
    }

    /**
     * Get associated price modifiers
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getServicePriceModifiers()
    {
        if ( null === $this->_priceModifiers )
        {
            $model                 = new Application_Model_ServiceModifier();
            $this->_priceModifiers = $model->fetchAll( "service_id = '" . $this->id . "'" );
        }
        return $this->_priceModifiers;
    }

    /**
     * Has turnaround time
     *
     * Check if a turnaround time is available for this service
     *
     * @param int $turnaroundId Turnaround time
     *
     * @return bool
     */
    public function hasTurnaroundTime( $turnaroundId )
    {
        return $this->getServiceTurnaroundTime( $turnaroundId );
    }

    /**
     * Has speaker number
     *
     * Check if a speaker number is available for this service
     *
     * @param int $speakerNumberId Speaker Number ID
     *
     * @return bool
     */
    public function hasSpeakerNumber( $speakerNumberId )
    {
        return (bool) $this->getServiceSpeakerNumber( $speakerNumberId );
    }

    /**
     * Has price modifier
     *
     * Check if a price modifier is available for this service
     *
     * @param int $modifierId Price modifier ID
     *
     * @return bool
     */
    public function hasPriceModifier( $modifierId )
    {
        return (bool) $this->getServicePriceModifier( $modifierId );
    }

    /**
     * Get service turnaround
     *
     * @param int $turnaroundId Turnaround time ID
     *
     * @return Zend_Db_Table_Row
     */
    public function getServiceTurnaroundTime( $turnaroundId )
    {
        if ( count( $this->getServiceTurnaroundTimes() ) > 0 )
        {
            foreach ( $this->getServiceTurnaroundTimes() as $turnaroundTime )
            {
                if ( $turnaroundId == $turnaroundTime->turnaround_time_id )
                {
                    return $turnaroundTime;
                }
            }
        }
    }

    /**
     * Get service speaker number
     *
     * @param int $speakerNumberId Speaker number ID
     *
     * @return Zend_Db_Table_Row
     */
    public function getServiceSpeakerNumber( $speakerNumberId )
    {
        if ( count( $this->getServiceSpeakerNumbers() ) > 0 )
        {
            foreach ( $this->getServiceSpeakerNumbers() as $speakerNumber )
            {
                if ( $speakerNumberId == $speakerNumber->speaker_number_id )
                {
                    return $speakerNumber;
                }
            }
        }
    }

    /**
     * Get service price modifiers
     *
     * @param int $modifierId Price modifier ID
     *
     * @return Zend_Db_Table_Row
     */
    public function getServicePriceModifier( $modifierId )
    {
        if ( count( $this->getServicePriceModifiers() ) > 0 )
        {
            foreach ( $this->getServicePriceModifiers() as $modifier )
            {
                if ( $modifierId == $modifier->price_modifier_id )
                {
                    return $modifier;
                }
            }
        }
    }

    /**
     * Calculate a rate for an audio job based on the price modifiers and selected options
     *
     * @param int                        $speakerNumbersId Speaker numbers ID
     * @param int                        $turnaroundTimeId Turnaround time ID
     * @param Zend_Db_Table_Rowset|array $modifiers        Price modifiers
     *
     * @return float
     */
    public function calculatePrice( $speakerNumbersId, $turnaroundTimeId, $modifiers )
    {
        $percentage       = 0;
        $typistPercentage = 0;
        $intervalModifier = 0;

        $model = new Application_Model_ServiceModifier();

        if ( count( $modifiers ) > 0 )
        {
            foreach ( $modifiers as $modifier )
            {
                if ( $modifier instanceof Application_Model_AudioJobsPriceModifiers_Row )
                {
                    $serviceModifier = $modifier->getServicePriceModifier();
                }
                else
                {
                    $serviceModifier = $model->fetchRow( 'id = ' . $modifier );
                }
                $priceModifier = $serviceModifier->getPriceModifier();

                if ( $priceModifier->type === Application_Model_PriceModifier_Row::TYPE_PERCANTAGE )
                {
                    $percentage += $serviceModifier->modifier_value;
                }
                else
                {
                    $intervalModifier += $serviceModifier->modifier_value;
                }
                $typistPercentage += $serviceModifier->typist_percentage;
            }
        }

        $speakerNumber  = $this->getServiceSpeakerNumber( $speakerNumbersId );
        $turnaroundTime = $this->getServiceTurnaroundTime( $turnaroundTimeId );

        $percentage       += ( (float) $speakerNumber->percentage + (float) $turnaroundTime->percentage );
        $typistPercentage += ( (float) $speakerNumber->typist_percentage );

        $return = array(
            'rate'                   => ( $this->base_price + ( ( $this->base_price / 100 ) * $percentage ) ) / 4,
            'interval_rate_modifier' => $intervalModifier,
            'typist_rates'           => array(
                '1' => ( $this->typist_grade_1 + ( ( $this->typist_grade_1 / 100 ) * $typistPercentage ) ),
                '2' => ( $this->typist_grade_2 + ( ( $this->typist_grade_2 / 100 ) * $typistPercentage ) ),
                '3' => ( $this->typist_grade_3 + ( ( $this->typist_grade_3 / 100 ) * $typistPercentage ) ),
                '4' => ( $this->typist_grade_4 + ( ( $this->typist_grade_4 / 100 ) * $typistPercentage ) ),
                '5' => ( $this->typist_grade_5 + ( ( $this->typist_grade_5 / 100 ) * $typistPercentage ) ),
            )
        );
        return $return;
    }
}