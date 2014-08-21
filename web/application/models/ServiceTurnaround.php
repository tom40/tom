<?php

class Application_Model_ServiceTurnaround extends App_Db_Table
{
    /**
     * Table name
     * @param string
     */
    protected $_name = 's_service_turnaround';

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_ServiceTurnaround_Row';

    /**
     * Service turnarounds
     * @var array
     */
    protected $_serviceTurnarounds = array();

    /**
     * Get turnaround time as a row set
     *
     * @param int $serviceId Service ID
     *
     * @return Zend_Db_Table_Rowset Row set of Application_Model_TurnaroundTime_Row objects
     */
    public function getTurnaroundTimes( $serviceId )
    {
        $tas = null;
        if ( empty( $this->_serviceTurnarounds[$serviceId] ) )
        {
            $taRels = $this->fetchAll(
                 $this->select()
                 ->where(
                    'service_id = ?',
                    $serviceId
                 )
            );

            if ( count( $taRels ) > 0 )
            {
                $turnaroundTimes = array();
                foreach ( $taRels as $taRel )
                {
                    $turnaroundTimes[] = $taRel->turnaround_time_id;
                }
                $query                                 = "'" . implode( "','", $turnaroundTimes ) . "'";
                $taModel                               = new Application_Model_TurnaroundTime();
                $tas                                   = $taModel->fetchAll(
                     $taModel->select()
                         ->where(
                            'id IN(' . $query . ')'
                        )
                );
                $this->_serviceTurnarounds[$serviceId] = $tas;
            }
        }
        return $tas;
    }

    /**
     * Get service turnaround
     *
     * @param int $serviceId    Service ID
     * @param int $turnaroundId Turnaround ID
     *
     * @return Application_Model_TurnaroundTime_Row
     */
    public function getServiceTurnaround( $serviceId, $turnaroundId )
    {
        $turnArounds = $this->getTurnaroundTimes( $serviceId );
        if ( count( $turnArounds ) > 0 )
        {
            foreach ( $turnArounds as $turnAround )
            {
                if ( $turnAround->id == $turnaroundId )
                {
                    return $turnAround;
                }
            }
        }
    }

    /**
     * Update service turnaround times
     *
     * @param int   $serviceId Service ID
     * @param array $data      Turnaround data
     *
     * @return void
     */
    public function updateServiceTurnaroundTimes( $serviceId, $data )
    {
        $this->delete( 'service_id = ' . $serviceId );
        foreach( $data as $key => $value )
        {
            $row = $this->createRow();
            $row->service_id         = $serviceId;
            $row->turnaround_time_id = $key;
            $row->percentage         = $value;
            $row->save();
        }
    }
}
