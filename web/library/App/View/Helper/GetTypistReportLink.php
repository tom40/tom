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
class App_View_Helper_GetTypistReportLink extends Zend_View_Helper_Abstract
{

    public function getTypistReportLink($audioJobId)
    {
        $reportModel   = new Application_Model_Report();
        $currentUserId = Zend_Auth::getInstance()->getIdentity()->id;
        $reportId      = $reportModel->getTypistReport($currentUserId, $audioJobId);
        if (!empty($reportId))
        {
           if ($reportModel->isReportCompleted($reportId))
           {
               return $reportId;
           }
        }

        return null;
    }

}
