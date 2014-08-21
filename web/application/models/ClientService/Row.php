<?php

class Application_Model_ClientService_Row extends Zend_Db_Table_Row
{

    /**
     * Get Service
     *
     * @return Application_Model_Service_Row
     */
    public function getService()
    {
        $mapper = new Application_Model_Service();
        return $mapper->fetchRow( $mapper->select()->where( 'id = ?', $this->service_id ) );
    }

}