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
class App_View_Helper_UserHasJobAccess extends Zend_View_Helper_Abstract
{

    public function userHasJobAccess($userId, $jobId)
    {
        // Get Audio Job
        $jobMapper  = new Application_Model_JobMapper();
        $userMapper = new Application_Model_UserMapper();
        $aclMapper  = new Application_Model_AclMapper();

        $job  = $jobMapper->fetchById($jobId);
        $user = $userMapper->fetchById($userId);

        if (!empty($job) && !empty($user))
        {
            $jobAclResourceId = $job['acl_resource_id'];
            $userAclRoleId    = $user['acl_role_id'];

            if (!empty($jobAclResourceId) && !empty($userAclRoleId))
            {
                // Check if audio job resource is in the user resources
                $userResources = $aclMapper->fetchResourceIdsList($userAclRoleId, true);
                $resources     = implode(',', $userResources);
                if (in_array($jobAclResourceId, $userResources))
                {
                    return true;
                }
            }
        }
        return false;
    }

}
