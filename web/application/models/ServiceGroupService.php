<?php

class Application_Model_ServiceGroupService extends App_Db_Table
{
    /**
     * Table name
     * @param string
     */
    protected $_name = 's_service_group_service';

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_ServiceGroupService_Row';

    /**
     * Update service groups
     *
     * @param int   $groupId Group ID
     * @param array $services Service array
     *
     * @return void
     */
    public function updateGroupServices( $groupId, $services )
    {
        $this->delete( 'service_group_id = ' . $groupId );
        foreach ( $services as $service )
        {
            $row                   = $this->createRow();
            $row->service_group_id = $groupId;
            $row->service_id       = $service;
            $row->save();
        }
    }
}
