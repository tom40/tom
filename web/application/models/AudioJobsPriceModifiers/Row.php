<?php

class Application_Model_AudioJobsPriceModifiers_Row extends Zend_Db_Table_Row
{

    /**
     * Price modifiers
     * @param Application_Model_PriceModifier_Row
     */
    protected $_modifier;

    /**
     * Audio job
     * @param Application_Model_AudioJob_Row
     */
    protected $_audioJob;

    /**
     * Get price modifier
     *
     * @return Application_Model_PriceModifier_Row
     */
    public function getServicePriceModifier()
    {
        if ( null === $this->_modifier )
        {
            $model           = new Application_Model_ServiceModifier();
            $this->_modifier = $model->fetchRow( 'id = ' . $this->service_price_modifier_id );
        }
        return $this->_modifier;
    }

    /**
     * Get audio job
     *
     * @return Application_Model_AudioJob_Row
     */
    public function getAudioJob()
    {
        if ( null === $this->_audioJob )
        {
            $model           = new Application_Model_AudioJobMapper();
            $this->_audioJob = $model->fetchRow( 'id=' . $this->audio_job_id );
        }
        return $this->_audioJob;
    }

}