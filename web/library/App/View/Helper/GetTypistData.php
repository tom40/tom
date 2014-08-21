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
class App_View_Helper_GetTypistData extends Zend_View_Helper_Abstract
{

    public function getTypistData($userId)
    {
        $model = new Application_Model_TypistMapper();
        return $model->fetchByUserId($userId);
    }

}
