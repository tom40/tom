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
class App_View_Helper_DisplayShiftRange extends Zend_View_Helper_Abstract
{

    public function displayShiftRange($startDay, $startTime, $endDay, $endTime)
    {
        return '(' . $startDay . ' ' . $this->view->formatTime($startTime) . ' - ' . $endDay . ' ' . $this->view->formatTime($endTime) . ')';
    }

}
