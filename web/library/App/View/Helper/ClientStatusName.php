<?php

/**
 * @see Zend_View_Helper_Abstract
 */
require_once 'Zend/View/Helper/Abstract.php';

/**
 *
 * @category   App
 * @package    App_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2012 Take Note Typing
 * @version    $Id$
 */
class App_View_Helper_ClientStatusName extends Zend_View_Helper_Abstract
{

    public function clientStatusName( $audioJob )
    {
        $statusMapper = new Application_Model_AudioJobStatusMapper();
        $status       = $statusMapper->fetchRow( 'id = ' . $audioJob->status_id );

        if ( ( '1' == $status['typist_editable'] || '1' == $status['proofreader_editable'] )  && !empty( $audioJob->last_status_id ) )
        {
            $status = $statusMapper->fetchRow( 'id = ' . $audioJob->last_status_id );
        }

        if ( '1' == $status['complete'] )
        {
            return 'Complete';
        }
        else
        {
            return 'In Progress';
        }
    }

}