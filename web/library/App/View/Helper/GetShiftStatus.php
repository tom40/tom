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
class App_View_Helper_GetShiftStatus extends Zend_View_Helper_Abstract
{

    public function getShiftStatus($shiftId, $dayShift)
    {
        if (isset($dayShift['HOLIDAY']))
        {
            return 'HOLIDAY';
        }
        elseif (isset($dayShift[$shiftId]))
        {
            return $dayShift[$shiftId]['status'];
        }

        return null;
    }

}
