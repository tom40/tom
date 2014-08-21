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
class App_View_Helper_IsTypistOrProofreader extends Zend_View_Helper_Abstract
{
    /**
     * Check if the current user is a typist or a proofreader
     * 
     * @return bool
     */
    public function isTypistOrProofreader()
    {
        $this->_currentUserId = Zend_Auth::getInstance()->getIdentity()->id;
        $typistModel          = new Application_Model_TypistMapper();
        $proofreaderModel     = new Application_Model_ProofreaderMapper();
        $typistUser           = $typistModel->fetchByUserId($this->_currentUserId);
        $proofreaderUser      = $proofreaderModel->fetchByUserId($this->_currentUserId);

        if (!empty($typistUser) || !empty($proofreaderUser))
        {
            return true;
        }

        return false;
    }
}
