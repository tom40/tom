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
class App_View_Helper_CheckTranscriptionFileAccess extends Zend_View_Helper_Abstract
{

    public function checkTranscriptionFileAccess($audioJobId, $statusId)
    {
        if (Application_Model_UserMapper::RETURNED_TO_CLIENT_STATUS_ID == $statusId)
        {
            $transcriptionMapper = new Application_Model_TranscriptionFileMapper();
            $transcriptionFile   = $transcriptionMapper->fetchLatestByAudioJobId($audioJobId);

            $userMapper = new Application_Model_UserMapper;
            $user       = $userMapper->fetchById($transcriptionFile['created_user_id']);

            if (('4' == $user['acl_group_id'] || '1' == $user['acl_group_id']) && '1' == $transcriptionFile['proofread'])
            {
                return true;
            }

            $ajProofReader  = new Application_Model_AudioJobProofreaderMapper();
            $hasProofReader = $ajProofReader->fetchByAudioJobId($audioJobId);

            $proofReaderMapper   = new Application_Model_ProofreaderMapper();
            $proofReader         = $proofReaderMapper->fetchByUserId($transcriptionFile['created_user_id']);

            if (empty($hasProofReader) || !empty($proofReader))
            {
                return true;
            }

            return false;
        }

        return false;
    }

}
