<?php
/**
 * Created by JetBrains PhpStorm.
 * User: joemiddleton
 * Date: 22/08/2013
 * Time: 14:39
 * To change this template use File | Settings | File Templates.
 */

class App_Mail_StaffInvoiceEmail extends App_Mail_Mail
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
        $this->setSender( $config->app->email->defaultNoReply, $config->app->name );
        $this->setHtmlTemplate( '/_partials/email/staff-invoice/staff-invoice-status-change.phtml' );
        $this->addPartialOption('appName', $config->app->name);
    }

    /**
     * Set view data
     *
     * @param array $data Email view data
     *
     * @return self
     */
    public function setViewData( $data )
    {
        $this->addPartialOption( 'viewData', $data );
        return $this;
    }

}