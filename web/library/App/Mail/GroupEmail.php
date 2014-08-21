<?php

/**
 * App_Mail_GroupEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: App_Mail_GroupEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */

/**
 * App_Mail_GroupEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: App_Mail_GroupEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */
class App_Mail_GroupEmail extends App_Mail_Mail
{
    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
        parent::__construct();
        $config = Zend_Registry::get('config');
        $this->setSender($config->app->email->defaultNoReply, $config->app->name);
        $this->setReceiver($config->app->email->defaultEmail);
        $this->setSubject('Email from the Take Note Team');
        $this->setHtmlTemplate('/_partials/email/group/group-html-email.phtml');
        $this->addPartialOption('appName', $config->app->name);
        $this->addPartialOption('isExtraWorkEmail', false);
    }

    /**
     * Set the site url
     *
     * @param string $appUrl the site url
     *
     * @return App_Mail_NewUserEmail
     */
    public function setSiteUrl($appUrl)
    {
        $this->addPartialOption('appUrl', $appUrl);
        return $this;
    }

    /**
     * Set email message boxy
     *
     * @param string $messageBody the email body
     *
     * @return void
     */
    public function setMessageBody($messageBody)
    {
        $this->addPartialOption('message', $messageBody);
    }

    /**
     * Set audio jobs to be sent to users
     *
     * @param array $jobs list of jobs to be sent to the user
     *
     * @return void
     */
    public function setAudioJobs($jobs)
    {
        $this->addPartialOption('isExtraWorkEmail', true);
        $this->addPartialOption('jobs', $jobs);
    }

}
