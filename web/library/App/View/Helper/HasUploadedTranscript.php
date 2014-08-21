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
class App_View_Helper_HasUploadedTranscript extends Zend_View_Helper_Abstract
{

    public function hasUploadedTranscript($audioJobId, $userType, $userId = null)
    {
        if (!empty($userId))
        {
            if (Application_Model_User::PROOFREADER_USER === $userType)
            {
                $mapper = new Application_Model_AudioJobProofreaderMapper();
            }
            else
            {
                $mapper = new Application_Model_AudioJobTypistMapper();
            }
            return $mapper->hasTranscriptionFile($audioJobId, $userId);
        }

        return false;
    }

}
