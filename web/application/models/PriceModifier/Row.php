<?php

/**
 * @property mixed type
 */
class Application_Model_PriceModifier_Row extends Zend_Db_Table_Row
{

    const TYPE_COST       = 'c';
    const TYPE_PERCANTAGE = 'p';

    /**
     * Get type string
     *
     * @return string
     */
    public function getTypeString()
    {
        $typesArray = array(
            self::TYPE_COST       => 'Cost per 15m',
            self::TYPE_PERCANTAGE => '%'
        );
        return $typesArray[$this->type];
    }

}