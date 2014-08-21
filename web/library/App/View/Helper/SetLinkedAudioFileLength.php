<?php

/**
 * @see Zend_View_Helper_Abstract
 */
require_once 'Zend/View/Helper/Abstract.php';

/**
 * Combines the length for linked files on the main job
 * 
 * @category   App
 * @package    App_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2012 Take Note Typing
 * @version    $Id$
 */
class App_View_Helper_SetLinkedAudioFileLength extends Zend_View_Helper_Abstract
{

    public function setLinkedAudioFileLength($audioJobs)
    {
        if (!empty($audioJobs))
        {
            // Set for simple further manipulation
            $newAudioJobs = array();
            foreach($audioJobs as $aJob)
            {
                $newAudioJobs[$aJob['id']] = $aJob;
            }

            foreach ($newAudioJobs as $key => $audioJob)
            {
                if (!empty($audioJob['lead_id']))
                {
                    $newAudioJobs[$audioJob['lead_id']]['length_seconds'] += $audioJob['length_seconds'];
                    $newAudioJobs[$audioJob['id']]['length_seconds']      = 0;
                }
            }

            return $newAudioJobs;
        }

       return null;
    }

}
