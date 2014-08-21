<?php

class Application_Model_ServiceTurnaround_Row extends Zend_Db_Table_Row
{

    /**
     * Turnaround row object
     * @Application_Model_Turnaround_Row
     */
    protected $_turnaround;

    /**
     * Service row object
     * @Application_Model_Service_Row
     */
    protected $_service;

    /**
     * Get service object
     *
     * @return Application_Model_Row_Service
     */
    public function getService()
    {
        if ( null === $this->_service )
        {
            $model          = new Application_Model_ServiceTime();
            $this->_service = $model->fetchRow( $model->select()->where( 'id = ?', $this->service_id ) );
        }
        return $this->_service;
    }

    /**
     * Get turnaround row object
     *
     * @return Application_Model_TurnaroundTime_Row
     */
    public function getTurnaroundTime()
    {
        if ( null === $this->_turnaround )
        {
            $model             = new Application_Model_TurnaroundTime();
            $this->_turnaround = $model->fetchRow( $model->select()->where( 'id = ?', $this->turnaround_time_id ) );
        }
        return $this->_turnaround;
    }
}