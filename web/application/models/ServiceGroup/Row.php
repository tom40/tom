<?php

class Application_Model_ServiceGroup_Row extends Zend_Db_Table_Row
{

    /**
     * Services assigned to this group
     * @var Zend_Db_Table_Rowset
     */
    protected $_services;

    /**
     * Clients
     * @var Zend_Db_Table_Rowset
     */
    protected $_clients;

    /**
     * Pre delete action
     *
     * @return void
     */
    protected function _delete()
    {
        $model = new Application_Model_ServiceGroupService();
        $model->delete( 'service_group_id = ' . $this->id );

        $model = new Application_Model_ClientServiceGroup();
        $model->delete( 'service_group_id = ' . $this->id );
    }

    /**
     * Can this service be deleted
     *
     * If clients are assigned to this group, it can not be deleted
     *
     * @return bool
     */
    public function canDelete()
    {
        return !(bool) count( $this->getClients() );
    }

    /**
     * Get Services
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getServices()
    {
        if ( !empty( $this->_cleanData ) && null === $this->_services )
        {
            $model = new Application_Model_ServiceGroupService();
            $this->_services = $model->fetchAll( $model->select()->where( 'service_group_id = ?', $this->id ) );
        }
        return $this->_services;
    }

    /**
     * Get client assigned to this group
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getClients()
    {
        if ( null === $this->_clients )
        {
            $model          = new Application_Model_ClientServiceGroup();
            $this->_clients = $model->fetchAll( $model->select()->where( 'service_group_id = ?', $this->id ) );
        }
        return $this->_clients;
    }

    /**
     * Has service
     *
     * @param int $serviceId Service ID
     *
     * @return bool
     */
    public function hasService( $serviceId )
    {
        return (bool) $this->getService( $serviceId );
    }

    /**
     * Get service
     *
     * @param int $serviceId Service ID
     *
     * @return bool
     */
    public function getService( $serviceId )
    {
        if ( count( $this->getServices() ) > 0 )
        {
            foreach ( $this->getServices() as $service )
            {
                if ( $serviceId == $service->service_id )
                {
                    return $service;
                }
            }
        }
    }

    /**
     * Update Services
     *
     * @param array $services Array of service IDs
     *
     * @return void
     */
    public function updateServices( $services )
    {
        $model = new Application_Model_ServiceGroupService;
        $model->updateGroupServices( $this->id, $services );
    }
}