<?php

/**
 * App_Mail_ShareAccessEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: App_Mail_ShareAccessEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */

/**
 * App_Mail_ShareAccessEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: App_Mail_ShareAccessEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */
class App_Mail_ShareAccessEmail extends App_Mail_Mail
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
        $this->setSubject('You have been granted access to a project in the Take Note Portal');
        $this->setHtmlTemplate('/_partials/email/job/share-access-html-email.phtml');
        $this->addPartialOption('appName', $config->app->name);
    }

    public function setJob($jobId)
    {
        $jobMapper = new Application_Model_JobMapper();
        $data      = $jobMapper->fetchDataForAccessShare($jobId);
        $this->setViewData($data);
    }

    public function setReceiverName($name)
    {
        $this->addPartialOption('receiverName', $name);
    }

    /**
     * Set view data
     *
     * @param array $data Email view data
     *
     * @return self
     */
    public function setViewData($data)
    {
        $this->addPartialOption('viewData', $data);
        return $this;
    }
}
