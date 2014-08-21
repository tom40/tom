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
class App_View_Helper_DisplayAudioJobLength extends Zend_View_Helper_Abstract
{

    /**
     * Display the time length of an audio job
     *
     * The following line of code ensures that x minutes and (seconds > 0) = x+1 minutes
     * e.g. 17m 01s = 18m
     *
     * $lengthSeconds = $lengthSeconds + ((int) (bool) ($lengthSeconds % 60)) * (60 - ($lengthSeconds % 60));
     *
     * @param $lengthSeconds
     *
     * @return string
     */
    public function displayAudioJobLength($lengthSeconds)
    {
        $hours   = 0;
        $minutes = 0;

        if (!empty($lengthSeconds))
        {
            $lengthSeconds      = $lengthSeconds + ((int) (bool) ($lengthSeconds % 60)) * (60 - ($lengthSeconds % 60));
            $hours              = floor($lengthSeconds / (60 * 60));
            $divisionForMinutes = $lengthSeconds % (60 * 60);
            $minutes            = floor($divisionForMinutes / 60);
        }

        $html = $hours . 'h' . ' : ' . $minutes . 'm';
        return $html;
    }

}
