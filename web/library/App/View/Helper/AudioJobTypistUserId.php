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
class App_View_Helper_AudioJobTypistUserId extends Zend_View_Helper_Abstract
{

    public function audioJobTypistUserId($audioJobId)
    {
        $mapper = new Application_Model_AudioJobTypistMapper();
        $result = $mapper->fetchByAudioJobId($audioJobId, true);
        return $result['user_id'];
    }

}
