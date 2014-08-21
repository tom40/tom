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
class App_View_Helper_DisplayUnsplitLink extends Zend_View_Helper_Abstract
{
    const TYPIST_USER      = 'typist';
    const PROOFREADER_USER = 'proofreader';

    public function displayUnsplitLink($audioJobId, $userType)
    {
        $returnValue  = false;
        $mapper       = null;
        if ($userType == self::TYPIST_USER)
        {
            $mapper = new Application_Model_AudioJobTypistMapper();
        }
        else if ($userType == self::PROOFREADER_USER)
        {
            $mapper = new Application_Model_AudioJobProofreaderMapper();
        }

        if ($mapper->isAssignedSplitAudioJob($audioJobId))
        {
            $returnValue = true;

            if ($mapper->hasTranscriptionFile($audioJobId))
            {
                $returnValue = false;
            }
        }

        return $returnValue;
    }

}
