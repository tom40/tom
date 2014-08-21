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
class App_View_Helper_TypistPayPerMinute extends Zend_View_Helper_Abstract
{
    /**
     * Check if the current user is a typist or a proofreader
     *
     * @param int   $typist    Typist ID
     * @param array $audioJobs Audio jobs ID
     *
     * @return string
     */
    public function typistPayPerMinute( $typist, $audioJobs )
    {
        $payMapper     = new Application_Model_TranscriptionTypistPayrateMapper();
        $serviceMapper = new Application_Model_Service();

        if ( !is_array( $typist ) )
        {
            $typistMapper = new Application_Model_TypistMapper();
            $typist       = $typistMapper->fetchByUserId($typist);
        }

        $string = array();
        foreach ($audioJobs as $audioJob)
        {
            if ( !empty( $audioJob['service_id'] ) )
            {
                $grade   = 'typist_grade_' . $typist['payrate_id'];
                $service = $serviceMapper->fetchRow( 'id = ' . $audioJob['service_id'] );
                $ppm     = $service->{$grade};
            }
            else
            {
                $ppm = $payMapper->getPayPerMinute($audioJob['transcription_type_id'], $typist['payrate_id']);
            }

            if (empty($ppm))
            {
                $ppm = 'n/a';
            }
            $string[] = $ppm;
        }
        return implode(', ', $string);
    }
}