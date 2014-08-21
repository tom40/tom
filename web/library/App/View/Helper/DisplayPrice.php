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
class App_View_Helper_DisplayPrice extends Zend_View_Helper_Abstract
{

    public function displayPrice($price, $twoDecimalPlaces = false)
    {
        if ($twoDecimalPlaces)
        {
            $price = number_format($price, 2);
        }

        return '&pound;' . $price;
    }

}
