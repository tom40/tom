<?php
/**
 * Created by JetBrains PhpStorm.
 * User: joemiddleton
 * Date: 20/08/2013
 * Time: 22:29
 * To change this template use File | Settings | File Templates.
 */

class Application_Model_User_Row extends Zend_Db_Table_Row
{
    /**
     * Get typist
     *
     * @return Zend_Db_Table_Row
     */
    public function getTypist()
    {
        $mapper = new Application_Model_TypistMapper();
        $typist = $mapper->fetchRow( 'user_id = ' . $this->id );

        if ( $typist )
        {
            return $typist;
        }

        return false;
    }

    /**
     * GEt proofreader
     *
     * @return Zend_Db_Table_Row
     */
    public function getProofReader()
    {
        $mapper      = new Application_Model_ProofreaderMapper();
        $proofreader = $mapper->fetchRow( 'user_id = ' . $this->id );
        if ( $proofreader )
        {
            return $proofreader;
        }

        return false;
    }


}