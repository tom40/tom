<?php

/**
 * App_Mail_NewUserEmail class file
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
 * App_Mail_NewUserEmail class file
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
class App_Mail_NewUserEmail extends App_Mail_Mail
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
        $this->setSubject('Welcome to the Take Note Portal');
        $this->setTextTemplate('/_partials/email/user/new-user-text-email.phtml');
        $this->setHtmlTemplate('/_partials/email/user/new-user-html-email.phtml');
        $this->addPartialOption('appName', $config->app->name);
    }

    /**
     * Stores the customers username
     *
     * @param string $userName the user name
     *
     * @return App_Mail_NewUserEmail
     */
    public function setName($name)
    {
        $this->addPartialOption('name', $name);
        return $this;
    }

    /**
     * Set the username
     *
     * @param string $username the username
     *
     * @return App_Mail_NewUserEmail
     */
    public function setUsername($username)
    {
        $this->addPartialOption('username', $username);
        return $this;
    }

    /**
     * Set the users password
     *
     * @param string $password the password
     *
     * @return App_Mail_NewUserEmail
     */
    public function setPassword($password)
    {
        $this->addPartialOption('password', $password);
        return $this;
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

}
