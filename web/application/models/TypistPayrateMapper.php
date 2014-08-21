<?php

class Application_Model_TypistPayrateMapper extends App_Db_Table
{

    const SUBSTANDARD_PAYRATE = '0.4';
    const REPLACEMENT_PAYRATE = '0.6';

    protected $_name = 'typist_payrate';

    /**
     * Get the pay rate grade as an array id => name
     *
     * @return array
     */
    public function getPayrateGradeArray()
    {
        $payrates   = $this->fetchAll();
        $returnData = array();
        foreach ($payrates as $payrate)
        {
            $returnData[$payrate['id']] = $payrate['name'];
        }
        return $returnData;
    }
}