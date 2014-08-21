<?php

class App_Db_Manipulate
{

    /**
     * Convert a rowset or array to id => name key value pairs, suitable for drop downs
     *
     * @param array|Zend_Db_Table_Rowset $data  Data to manipulate
     * @param array                $array Build on this array, defaults to empty array
     *
     * @return array
     */
    public static function convertToDropDown( $data, $array = array() )
    {
        foreach ( $data as $row )
        {
            $array[ $row->id ] = $row->name;
        }
        return $array;
    }

}