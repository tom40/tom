<?php

/**
 * App_Mail_ReportEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: App_Mail_ReportEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */

/**
 * App_Mail_ReportEmail class file
 *
 * PHP Version 5.3
 *
 * @category   Take Note Typing
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: App_Mail_ReportEmail.php 170 2011-10-13 14:41:43Z saladj $
 * @since      1.0
 */
class App_Mail_ReportEmail extends App_Mail_Mail
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
        $this->setSubject('Proofreaders report notification');
        $this->setHtmlTemplate('/_partials/email/report/report-html-email.phtml');
        $this->addPartialOption('appName', $config->app->name);
    }

    /**
     * Set the site url
     *
     * @param string $appUrl the site url
     *
     * @return App_Mail_ReportEmail
     */
    public function setSiteUrl($appUrl)
    {
        $this->addPartialOption('appUrl', $appUrl);
        return $this;
    }

    /**
     * Set the report url
     *
     * @param string $url the report url
     *
     * @return App_Mail_ReportEmail
     */
    public function setReportUrl($url)
    {
        $this->addPartialOption('reportUrl', $url);
        return $this;
    }

    /**
     * Set report id
     *
     * @param int $reportId the report id
     *
     * @return App_Mail_ReportEmail
     */
    public function setReportId($reportId)
    {
        $this->addPartialOption('reportId', $reportId);
        return $this;
    }

    /**
     * Set audio job file name
     *
     * @param string $filename the audio job file name
     *
     * @return App_Mail_ReportEmail
     */
    public function setAudioJobFilename($filename)
    {
        $this->addPartialOption('fileName', $filename);
        return $this;
    }

    /**
     * Set project name
     *
     * @param string $name the project name
     *
     * @return App_Mail_ReportEmail
     */
    public function setProjectFilename($name)
    {
        $this->addPartialOption('projectName', $name);
        return $this;
    }

}
