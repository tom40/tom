<?php

class Application_Model_ServiceSpeakerNumber extends App_Db_Table
{
    /**
     * Table name
     * @param string
     */
    protected $_name = 's_service_speaker_number';

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_ServiceSpeakerNumber_Row';

    /**
     * Update service speakers
     *
     * @param int   $serviceId  Service ID
     * @param array $priceData  Turnaround data
     * @param array $typistData Typist data
     *
     * @return void
     */
    public function updateServiceSpeakers( $serviceId, $priceData, $typistData )
    {
        $this->delete( 'service_id = ' . $serviceId );
        foreach( $priceData as $key => $value )
        {
            $row = $this->createRow();
            $row->service_id         = $serviceId;
            $row->speaker_number_id  = $key;
            $row->percentage         = $value;
            $row->typist_percentage  = $typistData[$key];
            $row->save();
        }
    }
}
