<?php

/**
 * App_Mail_PasswordResetEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: App_Mail_PasswordResetEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */

/**
 * App_Mail_PasswordResetEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: App_Mail_PasswordResetEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */
class App_Mail_PasswordResetEmail extends App_Mail_Mail
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
        $this->setSubject('Password reset request');
        $this->setTextTemplate('/_partials/email/amnesia/password-reset-text-email.phtml');
        $this->setHtmlTemplate('/_partials/email/amnesia/password-reset-html-email.phtml');

        $this->addPartialOption('appName', $config->app->name);
    }

    /**
     * Stores the password reset url
     *
     * @param type $passwordResetUrl the password reset url
     *
     * @return App_Mail_PasswordResetEmail
     */
    public function setPasswordResetUrl($passwordResetUrl)
    {
        $this->addPartialOption('passwordResetUrl', $passwordResetUrl);
        return $this;
    }

    /**
     * Stores the sender's app name and url
     *
     * @param string $appName the app name
     * @param string $appUrl  the app url
     *
     * @return App_Mail_PasswordResetEmail
     */
    public function setSenderWebsite($appUrl)
    {
        $this->addPartialOption('appUrl', $appUrl);
        return $this;
    }

    /**
     * Stores the customers username
     *
     * @param string $userName the user name
     *
     * @return App_Mail_PasswordResetEmail
     */
    public function setCustomerUserName($userName)
    {
        $this->addPartialOption('username', $userName);
        return $this;
    }

    /**
     * Stores the password reset key
     *
     * @param string $passwordResetKey the password reset ket
     *
     * @return App_Mail_PasswordResetEmail
     */
    public function setPasswordResetKey($passwordResetKey)
    {
        $this->addPartialOption('passwordResetKey', $passwordResetKey);
        return $this;
    }

}
