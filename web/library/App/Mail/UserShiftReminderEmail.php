<?php

/**
 * App_Mail_UserShiftReminderEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: App_Mail_NewUserEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */

/**
 * App_Mail_UserShiftReminderEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: App_Mail_UserShiftReminderEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */
class App_Mail_UserShiftReminderEmail extends App_Mail_Mail
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $config = Zend_Registry::get('config');
        $this->setSender($config->app->email->defaultNoReply, $config->app->name);
        $this->setReceiver($config->app->email->defaultEmail);
        $this->setSubject('Shiftbooker reminder');
        $this->setHtmlTemplate('/_partials/email/shift-booker/reminder-html-email.phtml');
        $this->addPartialOption('appName', $config->app->name);
        $this->addPartialOption('isExtraWorkEmail', false);

        $headerImagePath = $config->app->system->url . '/images/email-header.gif';
        $this->addPartialOption('headerImagePath', $headerImagePath);
    }

    /**
     * Set the site url
     *
     * @param string $appUrl the site url
     *
     * @return App_Mail_UserShiftReminderEmail
     */
    public function setSiteUrl($appUrl)
    {
        $this->addPartialOption('appUrl', $appUrl);
    }
}
