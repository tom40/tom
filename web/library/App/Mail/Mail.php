<?php

/**
 * App_Mail_Mail class file
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: Mail.php 1105 2012-06-26 13:57:21Z jsalad $
 * @since      1.0
 */

/**
 * App_Mail_Mail class file
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 jsalad.
 * @version    $Id: Mail.php 1105 2012-06-26 13:57:21Z jsalad $
 * @since      1.0
 */
class App_Mail_Mail
{
    /**
     * The sender
     *
     * @var string
     */
    protected $_sender;

    /**
     * The sender name
     *
     * @var string
     */
    protected $_senderName;

    /**
     * The list of receivers
     *
     * @var string
     */
    protected $_receivers;

    /**
     * Holds the data sent to the partials (templates)
     *
     * @var string
     */
    protected $_partialOptions;

    /**
     * The email data
     *
     * @var string
     */
    protected $_data;

    /**
     * The view
     *
     * @var string
     */
    protected $_view;

    /**
     * The template for HTML emails
     *
     * @var string
     */
    protected $_templateHtml;

    /**
     * The template for TEXT emails
     *
     * @var string
     */
    protected $_templateText;

    /**
     * Set subject
     *
     * @var string
     */
    protected $_subject;

    /**
     * Errors generated when validating mail data supplied
     *
     * @var string
     */
    protected $_errors;

    /**
     * Attachments
     *
     * @var string
     */
    protected $_attachment = array();

    /**
     * Bcc emails
     *
     * @var string
     */
    protected $_bbc = array();

    /**
     * Constructor
     *
     * @param string $sender   the senders email
     * @param string $receiver the receivers email
     * @param string $subject  the subject of the email
     * @param string $data     the data to be sent
     *
     * @return self
     */
    public function __construct($sender = null, $receiver = null, $subject = null, $data = null)
    {
        $this->_sender      = $sender;
        $this->_receivers   = array();
        $this->_data        = $data;
        $this->_subject     = $subject;

        // Set receiver if supplied
        if (!empty($receiver)) {
            $this->_receivers[] = $receiver;
        }
    }

    /**
     * This function is used for generating unique filenames for emails
     * messages that are sent out using Zend_Mail.
     *
     * @deprecated This is currently used for testing Zend_Mail.
     *
     * @param Zend_Mail_Transport_Abstract $transport
     * @return string
     */
    public function generateMailFilename( $transport )
    {
        // Return the file name.
        $recipients = explode(',', $transport->recipients);
        if (!empty($recipients))
        {
          $recipients = $recipients[0];
        }
        return 'email_' . date('U') . '_' . $recipients . '_' . mt_rand() . '.eml';
    }

    /**
     * Turns array keys into presentable names
     *
     * @param array $data the data to format
     *
     * @return array $newData
     */
    public function generatePrettyFormat($data)
    {

        $newData = array();
        // Create a pretty format for keys
        foreach ($data as $key => $value) {
            $key           = str_replace('_', ' ', $key);
            $key           = ucfirst($key);
            $newData[$key] = $value;
        }

        return $newData;
    }

    /**
     * Add attachments
     *
     * @param string $name       the name of the attachment
     * @param mixed $attachment  the attachment
     *
     * @return self
     */
    public function addAttachment($name, $attachment)
    {
        $this->_attachment[$name] = $attachment;
        return $this;
    }

    /**
     * Clear attachments
     *
     * @return void
     */
    public function flushAttachments()
    {
        $this->_attachment = array();
    }

    /**
     * Sends the email
     *
     * @param mixed $attachment the attachment
     *
     * @return void
     */
    public function sendMail()
    {
        // Continue if email has all necessary parameters
        if ($this->isValid())
        {
            $config = Zend_Registry::get('config');

            // Instantiate the App Mail class.
            $mail = new App_Mail('UTF-8');

            if (!empty($this->_senderName))
            {
                $mail->setFrom($this->getSender(), $this->_senderName);
            }
            else
            {
                $mail->setFrom($this->getSender());
            }

            // Set Default Reply To
            if (!empty($config->app->email->defaultNoReply)) {
                $mail->setDefaultReplyTo($config->app->email->defaultNoReply);
            } else {
                $mail->setDefaultReplyTo($this->getSender());
            }

            // Set subject
            $mail->setSubject($this->getSubject());

            // Set add to
            $mail->addTo($this->getReceivers());

            // Default data container
            $this->addPartialOption('data', $this->getData());

            // Render the HTML email message.
            $messageHtml = $this->_view->partial($this->getHtmlTemplate(), 'default', $this->_getPartialOptions());

            // Set the html message.
            $mail->setBodyHtml($messageHtml);

            // Add attachments
            $this->_addAttachments($mail);

            $mail = $this->_addCcs($mail);
            $mail = $this->_addBccs($mail);

            if (empty($this->_templateText)) {
                // Generate the text message.
                $messageText = strip_tags($messageHtml);
            } else {
                // Render the TEXT email message.
                $messageText = $this->_view->partial($this->getTextTemplate(), 'default', $this->_getPartialOptions());
            }

            // Unset the view.
            unset($this->_view);

            // Set the text message.
            $mail->setBodyText($messageText);

            // Only send the email if there is a recipients
            $receivers = $this->getReceivers();

            if (!empty($receivers)) {
                // Send the message.
                $mail->send();
            }

            // Unset the message.
            unset($mail);
            return true;
        }
        return false;
    }

    /**
     * Add attachments
     *
     * @param Zend_Mail $mail
     *
     * @return Zend_Mail
     */
    protected function _addAttachments($mail)
    {
        if (!empty($this->_attachment)) {
            foreach ($this->_attachment as $name => $attachment) {
                $file = $mail->createAttachment($attachment);
                $file->filename = $name;
            }
        }

        return $mail;
    }

    /**
     * Add Bcc emails
     *
     * @param Zend_Mail $mail
     *
     * @return Zend_Mail
     */
    protected function _addBccs($mail)
    {
        if (!empty($this->_bcc)) {
            foreach ($this->_bcc as $email) {
                $mail->addBcc($email);
            }
        }
        return $mail;
    }

    /**
     * Add CC emails
     *
     * @param Zend_Mail $mail
     *
     * @return Zend_Mail
     */
    protected function _addCcs($mail)
    {
        $config = Zend_Registry::get('config');
        // CC into all emails
        $mail->addBcc($config->app->email->systemEmail);

        return $mail;
    }

    public function getHtmlContents()
    {
        return $this->_view->partial($this->getHtmlTemplate(), 'default', array('data' => $this->getData()));
    }

    /**
     * Adds data to the partial sent to the template
     *
     * @param string $key   the key of the data
     * @param string $value the value of the data
     *
     * @return void
     */
    protected function addPartialOption($key, $value)
    {
        $this->_partialOptions[$key] = $value;
    }

    /**
     * Returns data to the partial sent to the template
     *
     * @return array
     */
    protected function _getPartialOptions()
    {
        return $this->_partialOptions;
    }

    /**
     * Checks if all the data necessary to send an email have been supplied
     *
     * @return bool
     */
    public function isValid()
    {
        // Reset the error messages
        $this->_resetErrors();

        // Sender must be supplied
        if (false === $this->getSender()) {
            $this->_addError('empty', 'sender');
        }

        // A receiver must be supplied
        if (false === $this->getReceivers()) {
            $this->_addError('empty', 'receivers');
        }

        // Data to send must be supplied
        if (false === $this->getData()) {
            $this->_addError('empty', 'data');
        }

       // A view is not supplied
        if (false === $this->_view) {
            $this->_addError('empty', 'view');
        }

       // A view is not supplied
        if ((false === $this->getHtmlTemplate()) || (false === $this->getTextTemplate())) {
            $this->_addError('empty', 'email template');
        }
        // There are no errors, email is valid
        if (!$this->hasErrors()) {
            return true;
        }

        // There are no errors, email is valid
        return false;
    }

    /**
     * Set View
     *
     * @param string $view the view
     *
     * @return self Fluent interface
     */
    public function setView($view)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Set the HTML email template
     *
     * @param string $template the email template to use
     *
     * @return self Fluent interface
     */
    public function setHtmlTemplate($template)
    {
        $this->_templateHtml = $template;
        return $this;
    }

    /**
     * Returns the HTML email template
     *
     * @return string
     */
    public function getHtmlTemplate()
    {
        return $this->_templateHtml;
    }

    /**
     * Set the TEXT email template
     *
     * @param string $template the email template to use
     *
     * @return self Fluent interface
     */
    public function setTextTemplate($template)
    {
        $this->_templateText = $template;
        return $this;
    }

    /**
     * Returns the TEXT email template
     *
     * @return string
     */
    public function getTextTemplate()
    {
        return $this->_templateText;
    }

    /**
     * Set Sender
     *
     * @param string $senderEmail the senders email
     * @param string $senderName  the senders name
     *
     * @return self Fluent interface
     */
    public function setSender($senderEmail, $senderName = null)
    {
        $this->_sender     = $senderEmail;
        $this->_senderName = $senderName;
        return $this;
    }

    /**
     * Returns the sender
     *
     * @return void
     */
    public function getSender()
    {
        return $this->_sender;
    }

    /**
     * Add a bcc email
     *
     * @param string $bccEmail
     *
     * @return void
     */
    public function addBcc($bccEmail)
    {
        $this->_bcc[] = $bccEmail;
        return $this;
    }

    /**
     * Add a receiver
     *
     * @param string $receiverEmail the receivers email
     *
     * @return self
     */
    public function addReceiver($receiverEmail)
    {
        if (!empty($receiverEmail)) {
            $this->_receivers[] = $receiverEmail;
        }
        return $this;
    }

    /**
     * Set a single receiver email. Resets receivers to a new array
     *
     * @param string $receiverEmail the receivers email
     *
     * @return self Fluent interface
     */
    public function setReceiver($receiverEmail)
    {
        if (!empty($receiverEmail)) {
            $this->_receivers   = array();
            $this->_receivers[] = $receiverEmail;
        }
        return $this;
    }

    /**
     * Returns the receivers
     *
     * @return string
     */
    public function getReceivers()
    {
        return $this->_receivers;
    }

    /**
     * Set the data to send
     *
     * @param array $data the data to send
     *
     * @return self Fluent interface
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Returns the data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Set the email subject
     *
     * @param string $subject the subject
     *
     * @return self Fluent interface
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
        return $this;
    }

    /**
     * Returns the email subject
     *
     * @return string $subject
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * Add an error message
     *
     * @param string $type  the type of error (i.e EMPTY, INVALIDEMAIL etc)
     * @param string $error the error
     *
     * @return void
     */
    protected function _addError($type, $error)
    {
        $this->_errors[] = array('type' => $type, 'error' => $error);
    }

    /**
     * Reset errors
     *
     * @return void
     */
    protected function _resetErrors()
    {
        $this->_errors = array();
    }

    /**
     * Return whether there are any errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        if (!empty($this->_errors)) {
            return true;
        }

        return false;
    }

    /**
     * Return errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }
}
