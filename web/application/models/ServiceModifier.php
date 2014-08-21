<?php

class Application_Model_ServiceModifier extends App_Db_Table
{
    /**
     * Table name
     * @param string
     */
    protected $_name = 's_service_price_modifier';

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_ServiceModifier_Row';

    /**
     * Update service speakers
     *
     * <ul>
     * <li>Fetch current service modifiers and delete, if allowed, those not provided by $priceData.</li>
     * <li>Iterate through $priceData, update existing service modifiers and create new ones where required</li>
     * </ul>
     *
     * @param int   $serviceId  Service ID
     * @param array $priceData  Turnaround data
     * @param array $typistData Typist data
     *
     * @return void
     */
    public function updateServiceModifiers( $serviceId, $priceData, $typistData )
    {
        $modifiers        = $this->fetchAll( 'service_id = ' . $serviceId );
        $currentModifiers = array();

        foreach ( $modifiers as $modifier )
        {
            if ( !array_key_exists( $modifier->price_modifier_id, $priceData ) && $modifier->canDelete() )
            {
                $modifier->delete();
            }
            else
            {
                $currentModifiers[ $modifier->price_modifier_id ] = $modifier;
            }
        }

        foreach( $priceData as $key => $value )
        {
            if ( isset( $currentModifiers[ $key ] ) )
            {
                $row = $currentModifiers[ $key ];
            }
            else
            {
                $row = $this->createRow();
            }
            $row->service_id         = $serviceId;
            $row->price_modifier_id  = $key;
            $row->modifier_value     = $value;
            $row->typist_percentage  = $typistData[ $key ];
            $row->save();
        }
    }
}
