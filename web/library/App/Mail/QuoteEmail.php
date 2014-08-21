<?php

/**
 * App_Mail_QuoteEmail class file
 *
 * PHP Version 5.3
 *
 * @category   TakeNote
 * @package    Mail
 * @subpackage Quote
 * @copyright  Copyright (c) 2011-2011 One Result Ltd. (http://www.oneresult.co.uk)
 * @version    $Id:$
 * @since      1.0
 */

/**
 * App_Mail_QuoteEmail class
 *
 * @category   TakeNote
 * @package    Mail
 * @subpackage Quote
 * @copyright  Copyright (c) 2011-2011 One Result Ltd. (http://www.oneresult.co.uk)
 * @version    $Id:$
 * @since      1.0
 */
class App_Mail_QuoteEmail extends App_Mail_Mail
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
        $this->setSubject('Your quote from Take Note');
        $this->setHtmlTemplate('/_partials/email/quote/quote-email.phtml');
        $this->addPartialOption('appName', $config->app->name);
    }

    /**
     * Set view data
     *
     * @param array $viewData Email view data
     *
     * @return self
     */
    public function setViewData($viewData)
    {
        $this->addPartialOption('viewData', $viewData);
        return $this;
    }

}
