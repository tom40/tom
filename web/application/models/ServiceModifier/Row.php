<?php

class Application_Model_ServiceModifier_Row extends Zend_Db_Table_Row
{

    /**
     * Price Modifier
     * @var Application_Model_PriceModifier_Row
     */
    protected $_priceModifier;

    /**
     * Can delete
     * @var bool
     */
    protected $_canDelete;

    /**
     * Get Service
     *
     * @return Application_Model_PriceModifier_Row
     */
    public function getPriceModifier()
    {
        if ( null === $this->_priceModifier )
        {
            $mapper               = new Application_Model_PriceModifier();
            $this->_priceModifier = $mapper->fetchRow( $mapper->select()->where( 'id = ?', $this->price_modifier_id ) );
        }
        return $this->_priceModifier;
    }

    /**
     * Can delete
     *
     * If an audio job has this price modifier assigned, it can not be deleted
     *
     * @return bool
     */
    public function canDelete()
    {
        if ( null === $this->_canDelete )
        {
            $count   = 0;
            $model   = new Application_Model_AudioJobsPriceModifiers();
            $results = $model->fetchAll( 'service_price_modifier_id = ' . $this->id );

            if ( count( $results ) > 0 )
            {
                foreach( $results as $result )
                {
                    if ( null === $result->getAudioJob()->deleted )
                    {
                        $count++;
                    }
                }
            }

            $this->_canDelete = !( ( bool ) $count );
        }
        return $this->_canDelete;
    }

}