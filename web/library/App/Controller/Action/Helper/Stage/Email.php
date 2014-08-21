<?php

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';


/**
 * Email notification
 *
 * App_Controller_Action_Helper_Email provides email notification support to a controller.
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @package    Controller
 * @subpackage Controller_Action
 * @copyright Copyright (c) 2012 Take Note
 */
class App_Controller_Action_Helper_Stage_Email extends App_Controller_Action_Helper_Email
{

    /**
     * Attach transcription file to email
     *
     * @param Zend_Mail $mail Mail object
     * @param array     $data Data array
     *
     * @return Zend_Mail
     */
    protected function _attachTranscription($mail, $data)
    {
        if (!file_exists('../data/transcription/' . $data['id']))
        {
            $fileContents = 'TEST DATA - File doesn\'t exist (email generated by stage server)';
        }
        else
        {
            $fileContents = file_get_contents('../data/transcription/' . $data['id']);
        }
        $mail->createAttachment(
            $fileContents,
            $data['mime_type'],
            Zend_Mime::DISPOSITION_ATTACHMENT,
            Zend_Mime::ENCODING_BASE64,
            $data['file_name']
        );

        return $mail;
    }

}