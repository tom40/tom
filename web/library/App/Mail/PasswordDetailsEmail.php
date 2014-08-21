<?php

/**
 * App_Mail_PasswordDetailsEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    App
 * @subpackage Mail
 * @copyright  opyright (c) 2012-2013 Take Note
 * @version    $Id: App_Mail_PasswordDetailsEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */

/**
 * App_Mail_PasswordDetailsEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: App_Mail_PasswordDetailsEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */
class App_Mail_PasswordDetailsEmail extends App_Mail_PasswordResetEmail
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
        $this->setSubject('Take Note - Your New Password');
        $this->setTextTemplate('/_partials/email/amnesia/forgotpassword-text-email.phtml');
        $this->setHtmlTemplate('/_partials/email/amnesia/forgotpassword-html-email.phtml');
    }

     /**
     * Stores the customers new password
     *
     * @param string $password the user password
     *
     * @return App_Mail_PasswordResetEmail
     */
    public function setPassword($password)
    {
        $this->addPartialOption('password', $password);
        return $this;
    }
}
