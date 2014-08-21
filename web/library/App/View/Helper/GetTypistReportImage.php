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
class App_View_Helper_GetTypistReportImage extends Zend_View_Helper_Abstract
{

    public function getTypistReportImage($userId, $audioJobId)
    {
        $reportModel   = new Application_Model_Report();
        $reportId      = $reportModel->getTypistReport($userId, $audioJobId);
        if (!empty($reportId))
        {
           if ($reportModel->isReportCompleted($reportId))
           {
               return '<img src="/images/report_icon_complete.png" alt="Complete Report" />';
           }
        }

        return '<img src="/images/report_icon.png" alt="Incomplete Report" />';
    }

}
