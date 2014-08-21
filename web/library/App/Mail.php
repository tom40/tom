<?php

/**
 * Zend Framework
 *
 * PHP Version 5.3
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    App
 * @subpackage App
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Mail.php 181 2011-10-13 16:11:47Z saladj $
 * @since      1.0
 */

/**
 * Extended Mail class for Keltic Website.
 *
 * @package    App
 * @subpackage App
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Mail.php 181 2011-10-13 16:11:47Z saladj $
 * @since      1.0
 */
class App_Mail extends Zend_Mail
{


    /**
     * BCC Recipients
     * @var array
     */
    protected $_bccReciepients = array();

    /**
     * CC Recipients
     * @var array
     */
    protected $_ccReciepients = array();

    /**
     * Recipients
     * @var array
     */
    protected $_reciepients = array();

    /**
     * An email address to overide any added email addresses
     *
     * Mainly for development purposes
     *
     * @var bool|string
     */
    protected static $_forcedEmailAddress = false;

    /**
     * Add an email address to override any added email addresses
     *
     * Forces emails to be sent to this email address, for development purposes
     *
     * @param string $forcedEmailAddress the email address to force through
     *
     * @return void
     */
    public static function setForcedEmailAddress($forcedEmailAddress)
    {
        self::$_forcedEmailAddress = $forcedEmailAddress;
    }

    /**
     * Adds To-header and recipient, $email can be an array, or a single string address
     *
     * If a forced email address is set
     *
     * <ul>
     * <li>If there are no other recipients, use the forced email address</li>
     * <li>If there are other recipients, add no email addres and return self</li>
     * </ul>
     *
     * Ensure recipients aren't added more than once
     *
     * Else, use the supplied email address
     *
     * @param string|array $email Add email address
     * @param string       $name  Add name
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function addTo($email, $name = '')
    {
        if (false !== self::$_forcedEmailAddress)
        {
            $email = self::$_forcedEmailAddress;
        }

        if (!is_array($email))
        {
            $email = array($email);
        }

        foreach ($email as $address)
        {
            if (isset($this->_bccReciepients[$address]))
            {
                unset($this->_bccReciepients[$address]);
            }

            if (isset($this->_ccReciepients[$address]))
            {
                unset($this->_ccReciepients[$address]);
            }

            if (!isset($this->_reciepients[$address]))
            {
                $this->_reciepients[$address] = true;
                parent::addTo($address, $name);
            }
        }

        return $this;
    }

    /**
     * Add a bcc recipient
     *
     * Ensure recipients aren't added more than once
     *
     * @param string|array $email Email address
     *
     * @return self
     */
    public function addBcc($email)
    {

        if (false !== self::$_forcedEmailAddress)
        {
            $email = self::$_forcedEmailAddress;
        }

        if (!is_array($email))
        {
            $email = array($email);
        }

        foreach ($email as $address)
        {
            if (
                !isset($this->_bccReciepients[$address])
                &&
                !isset($this->_ccReciepients[$address])
                &&
                !isset($this->_reciepients[$address]))
            {
                $this->_bccReciepients[$address] = true;
                parent::addBcc($address);
            }
        }
        return $this;
    }

    /**
     * Add CC recipeitns
     *
     * return bool
     */
    public function addCc($email, $name = '')
    {
        if (false !== self::$_forcedEmailAddress)
        {
            $email = self::$_forcedEmailAddress;
        }

        if (!is_array($email))
        {
            $email = array($email);
        }

        foreach ($email as $address)
        {
            if (
                !isset($this->_bccReciepients[$address])
                &&
                !isset($this->_ccReciepients[$address])
                &&
                !isset($this->_reciepients[$address]))
            {
                $this->_ccReciepients[$address] = true;
                parent::addCc($address, $name);
            }
        }
        return $this;
    }

    /**
     * This function is used for generating unique filenames for emails
     * messages that are sent out using Zend_Mail.
     *
     * @param Zend_Mail_Transport_Abstract $transport the transport
     *
     * @deprecated This is currently used for testing Zend_Mail.
     *
     * @return string
     */
    public function generateMailFilename( $transport )
    {
        // Return the file name.
        return 'email_' . date('U') . '_' . $transport->recipients . '_' . mt_rand() . '.eml';
    }

    /**
     * Returns the configured email signature.
     *
     * @return mixed
     */
    public static function getEmailSignature()
    {
        // Get the email signature.
        $signature = Application_Model_Module_Options::getInstance()->getOption(App_Module_Default_Version::getID(), 'system_email_signature');

        // Return the email signature.
        return $signature;
    }

    /**
     * Returns whether there is an email signature configured or not.
     *
     * @return bool
     */
    public static function hasEmailSignature()
    {
        // Get the email signature value.
        $signature = self::getEmailSignature();

        // Check whether there is an email signature set or not.
        return !is_null($signature);
    }
}