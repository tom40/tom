<?php

class Application_Model_ClientServiceGroup_Row extends Zend_Db_Table_Row
{

    /**
     * Client row object
     * @var Application_Model_Client_Row
     */
    protected $_client;

    /**
     * Service row object
     * @var Application_Model_Service_Row
     */
    protected $_service;

    /**
     * Get client row object
     *
     * @return Application_Model_Client_Row
     */
    public function getClient()
    {
        if ( null === $this->_client )
        {
            $model         = new Application_Model_ClientMapper();
            $this->_client = $model->fetchRow( "id = '" . $this->client_id . "'" );
        }
        return $this->_client;
    }

    /**
     * Get client row object
     *
     * @return Application_Model_Client_Row
     */
    public function getService()
    {
        if ( null === $this->_service )
        {
            $model          = new Application_Model_Service();
            $this->_service = $model->fetchRow( "id = '" . $this->service_group_id . "'" );
        }
        return $this->_service;
    }

}