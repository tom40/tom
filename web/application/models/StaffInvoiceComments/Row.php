<?php
/**
 * Created by JetBrains PhpStorm.
 * User: joemiddleton
 * Date: 21/08/2013
 * Time: 21:38
 * To change this template use File | Settings | File Templates.
 */

class Application_Model_StaffInvoiceComments_Row extends Zend_Db_Table_Row
{

    /**
     * Get user name
     *
     * @return string
     */
    public function getUser()
    {
        $mapper = new Application_Model_UserMapper();
        return $mapper->fetchRow( 'id =' . $this->_data['created_by'] );
    }

}