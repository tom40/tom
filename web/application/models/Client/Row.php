<?php

class Application_Model_Client_Row extends Zend_Db_Table_Row
{

    /**
     * Service Groups
     * @var Zend_Db_Table_Rowset
     */
    protected $_serviceGroups;

    /**
     * Additional Service Groups
     * @var Zend_Db_Table_Rowset
     */
    protected $_additionalServices;

    /**
     * Get client groups
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getServiceGroups()
    {
        if ( null == $this->_serviceGroups )
        {
            $mapper        = new Application_Model_ClientServiceGroup();
            $serviceMapper = new Application_Model_ServiceGroup();
            $serviceGroups = $mapper->fetchAll( $mapper->select()->where( 'client_id = ?', $this->id ) );

            if ( count( $serviceGroups ) > 0 )
            {
                $array = array();
                foreach ( $serviceGroups as $serviceGroup )
                {
                    $array[] = $serviceGroup->service_group_id;
                }
                $this->_serviceGroups = $serviceMapper->fetchAll( $serviceMapper->select()->where( 'id IN(?)', $array ) );
            }
        }
        return $this->_serviceGroups;
    }

    /**
     * Get a string of service group names
     *
     * @param string $separator Separator character
     *
     * @return string
     */
    public function getServiceGroupsString( $separator = ', ' )
    {
        $serviceGroups = $this->getServiceGroups();
        $array         = array();
        if ( count( $serviceGroups ) > 0 )
        {
            foreach ( $serviceGroups as $serviceGroup )
            {
                $array[] = $serviceGroup->name;
            }
            return trim( implode( $separator, $array ) );
        }
        return '';
    }

    /**
     * Has service group
     *
     * @param int $groupId Service group ID
     *
     * @return bool
     */
    public function hasServiceGroup( $groupId )
    {
        return (bool) $this->getServiceGroup( $groupId );
    }

    /**
     * Get service group
     *
     * @param int $groupId Service ID
     *
     * @return bool
     */
    public function getServiceGroup( $groupId )
    {
        if ( count( $this->getServiceGroups() ) > 0 )
        {
            foreach ( $this->getServiceGroups() as $group )
            {
                if ( $groupId == $group->id )
                {
                    return $group;
                }
            }
        }
    }

    /**
     * Update Service groups
     *
     * @param array $groups Group IDs
     *
     * @return void
     */
    public function updateServiceGroups( $groups )
    {
        $mapper         = new Application_Model_ClientServiceGroup();
        $serviceGroup   = new Application_Model_ServiceGroupService();
        $clientServices = new Application_Model_ClientService();

        $mapper->delete( 'client_id = ' . $this->id );

        if ( count( $groups ) > 0 )
        {
            $newGroups = array();
            foreach ( $groups as $groupId )
            {
                $row                   = $mapper->createRow();
                $row->client_id        = $this->id;
                $row->service_group_id = $groupId;
                $row->save();
                $newGroups[] = $groupId;

                $existingServices = $serviceGroup->fetchAll( 'service_group_id = ' . $groupId );
                foreach ( $existingServices as $service )
                {
                    $clientServices->delete( 'service_id = ' . $service->service_id . ' AND client_id = ' . $this->id ) ;
                }
            }
        }
    }

    /**
     * Delete client services
     */

    /**
     * Get additional services
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getAdditionalServices()
    {
        if ( null == $this->_additionalServices )
        {
            $mapper                    = new Application_Model_ClientService();
            $this->_additionalServices = $mapper->fetchAll( $mapper->select()->where( 'client_id = ?', $this->id ) );
        }
        return $this->_additionalServices;
    }

    /**
     * Has additional service
     *
     * @param int $serviceId Service ID
     *
     * @return bool
     */
    public function hasAdditionalService( $serviceId )
    {
        return (bool) $this->getAdditionalService( $serviceId );
    }

    /**
     * Get additional service
     *
     * @param int $serviceId Service ID
     *
     * @return bool
     */
    public function getAdditionalService( $serviceId )
    {
        if ( count( $this->getAdditionalServices() ) > 0 )
        {
            foreach ( $this->getAdditionalServices() as $service )
            {
                if ( $serviceId == $service->service_id )
                {
                    return $service;
                }
            }
        }
    }

    /**
     * Get all services available to the client
     *
     * @return array|Zend_Db_Table_Rowset
     */
    public function getAllServices()
    {
        $services = array();
        $groups   = $this->getServiceGroups();
        if ( count( $groups ) > 0 )
        {
            foreach ( $this->getServiceGroups() as $group )
            {
                $groupServices = $group->getServices();
                foreach ( $groupServices as $service )
                {
                    $services[] = $service->getService();
                }
            }
        }

        $additionalServices = $this->getAdditionalServices();
        if ( count( $additionalServices ) > 0 )
        {
            foreach ( $additionalServices as $service )
            {
                $services[] = $service->getService();
            }
        }

        if ( count( $services ) === 0 )
        {
            $serviceModel = new Application_Model_Service();
            $services     = $serviceModel->fetchAll();
        }

        return $services;
    }

    /**
     * Update additional services
     *
     * @param array $services Service IDs
     *
     * @return void
     */
    public function updateAdditionalServices( $services )
    {
        $mapper = new Application_Model_ClientService();
        $mapper->delete( 'client_id = ' . $this->id );
        if ( count( $services ) > 0 )
        {
            foreach ( $services as $serviceId )
            {
                $row             = $mapper->createRow();
                $row->client_id  = $this->id;
                $row->service_id = $serviceId;
                $row->save();
            }
        }
    }
}