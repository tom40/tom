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
class App_View_Helper_Acl extends Zend_View_Helper_Abstract
{

    /**
     * ACL plugin object
     * @var App_Controller_Plugin_Acl
     **/
    protected static $_aclPlugin;

    /**
     * Constructor
     *
     * @param App_Controller_Plugin_Acl $aclPlugin Acl plugin object
     *
     * @return self
     **/
    public static function setAclPlugin(App_Controller_Plugin_Acl $aclPlugin)
    {
        self::$_aclPlugin = $aclPlugin;
    }

    /**
     * Expose the acl plugin object
     *
     * @return App_Controller_Plugin_Acl
     */
    public function acl()
    {
        return self::$_aclPlugin;
    }

}