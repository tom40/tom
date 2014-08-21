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
class App_View_Helper_UserHasAudioJobAccess extends Zend_View_Helper_Abstract
{

    public function userHasAudioJobAccess($userId, $audioJobId)
    {
        // Get Audio Job
        $audioJobMapper = new Application_Model_AudioJobMapper();
        $userMapper     = new Application_Model_UserMapper();
        $aclMapper      = new Application_Model_AclMapper();

        $audioJob = $audioJobMapper->fetchById($audioJobId);
        $user     = $userMapper->fetchById($userId);

        if (!empty($audioJob) && !empty($user))
        {
            $audioJobAclResourceId = $audioJob['acl_resource_id'];
            $userAclRoleId         = $user['acl_role_id'];

            if (!empty($audioJobAclResourceId) && !empty($userAclRoleId))
            {
                // Check if audio job resource is in the user resources
                $userResources = $aclMapper->fetchResourceIdsList($userAclRoleId, true);
                $resources     = implode(',', $userResources);
                if (in_array($audioJobAclResourceId, $userResources))
                {
                    return true;
                }
            }
        }
        return false;
    }

}
