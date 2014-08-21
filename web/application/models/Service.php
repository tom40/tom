<?php

class Application_Model_Service extends App_Db_Table
{
    /**
     * Table name
     * @param string
     */
    protected $_name = 's_service';

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_Service_Row';

    /**
     * Fetch Row
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $offset OPTIONAL An SQL OFFSET value.
     *
     * @return Application_Model_Service_Row
     */
    public function fetchRow( $where = null, $order = null, $offset = null )
    {
        return parent::fetchRow( $where, $order, $offset );
    }

    /**
     * Get services not in the supplied groups
     *
     * @param Zend_Db_Table_Rowset|array $groupIds Array of group IDs
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getServicesFilteredGroups( $groupIds = array() )
    {
        if ( $groupIds instanceof Zend_Db_Table_Rowset )
        {
            $array = array();
            foreach ( $groupIds as $group )
            {
                $array[] = $group->id;
            }
            $groupIds = $array;
        }

        $serviceGroupMapper = new Application_Model_ServiceGroupService();
        $serviceMapper      = new Application_Model_Service();

        $where = null;

        if ( count( $groupIds ) > 0 )
        {
            $where = $serviceGroupMapper->select()->where( 'service_group_id NOT IN (?)', $groupIds );
        }
        $serviceIds = $serviceGroupMapper->fetchAll( $where );

        if ( count( $serviceIds ) > 0 )
        {
            $array = array();
            foreach ( $serviceIds as $service )
            {
                $array[] = $service->service_id;
            }
            $serviceIds   = $array;
            $serviceWhere = $serviceMapper->select()->where( 'id IN (?)', $serviceIds );
            return $serviceMapper->fetchAll( $serviceWhere );
        }
        return array();
    }
}
