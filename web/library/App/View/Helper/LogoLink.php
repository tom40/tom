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
class App_View_Helper_LogoLink extends Zend_View_Helper_Abstract
{

    /**
     * Check if the current user is a typist or a proofreader
     *
     * @return bool
     */
    public function logoLink()
    {
        $aclGroup = Zend_Auth::getInstance()->getIdentity()->acl_group_id;
        switch ($aclGroup)
        {
            case 1: // admin
                return $this->view->url(array('controller' => 'audio-job', 'action' => 'list'), null, false);
            case 2: // staff
                return $this->view->url(array('controller' => 'audio-job', 'action' => 'list-typist'), null, false);
                break;
            case 3: // client
                return $this->view->url(array('controller' => 'job', 'action' => 'list-client'), null, false);
                break;
        }
        return '/';
    }

}