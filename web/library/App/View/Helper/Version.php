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
 * @version    $Id: Version.php 341 2011-03-22 21:21:05Z ianmunday $
 */
class App_View_Helper_Version extends Zend_View_Helper_Abstract
{

    public function version($aclGroup = 0)
    {
        $return = 'Version ' . App_Version::getVersion();
        if (1 === $aclGroup)
        {
            $return .= ' <a href="/change-log.html" target="_blank">Version Change Log</a>';
        }
        return $return;
    }

}
