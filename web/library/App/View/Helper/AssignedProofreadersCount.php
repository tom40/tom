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
class App_View_Helper_AssignedProofreadersCount extends Zend_View_Helper_Abstract
{

    public function assignedProofreadersCount($audioJobId)
    {
        $mapper = new Application_Model_AudioJobProofreaderMapper();
        return $mapper->getAssignedProofreadersCount($audioJobId);
    }

}
