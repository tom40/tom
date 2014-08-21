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
class App_View_Helper_FormatTime extends Zend_View_Helper_Abstract
{

    public function formatTime($time)
    {
        if ('23:59' == $time || '23:59:00' == $time)
        {
            $time = '00:00';
        }

        return date('g:ia', strtotime($time));
    }

}
