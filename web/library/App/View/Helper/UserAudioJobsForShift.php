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
class App_View_Helper_UserAudioJobsForShift extends Zend_View_Helper_Abstract
{

    public function userAudioJobsForShift($userId, $shiftId, $userType = Application_Model_UsersShiftMapper::TYPIST_SHIFT)
    {
        if (!empty($shiftId))
        {
            if ($userType == Application_Model_UsersShiftMapper::PROOFREADER_SHIFT)
            {
                $model = new Application_Model_AudioJobProofreaderMapper();
            }
            else
            {
                $model = new Application_Model_AudioJobTypistMapper();
            }

            $jobs  = $model->fetchUserShiftJobs($userId, $shiftId);
            if (!empty($jobs))
            {
                $jobNames = '';
                foreach ($jobs as $job)
                {
                    if ( null !== $job['minutes_start'] && null !== $job['minutes_end'] )
                    {
                        $length = $job['minutes_end'] - $job['minutes_start'];
                    }
                    else
                    {
                        $length = floor($job['length_seconds'] / 60);
                    }

                    $jobNames[] = $length . substr($job['name'], 0, 1);
                }
                return '<strong>(' . implode(', ', $jobNames) . ')</strong>';
            }
            return null;
        }

        return null;
    }

}
