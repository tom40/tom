<?php

/**
 * Amnesia Controller
 *
 * PHP Version 5.3
 *
 * @category   Take Note
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: AmnesiaController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */

/**
 * Amnesia Controller
 *
 * PHP Version 5.3
 *
 * @category   Baker_Taylor
 * @package    App
 * @subpackage Mail
 * @copyright  Copyright (c) 2012-2013 Take Note
 * @version    $Id: AmnesiaController.php 200 2012-9-19 13:36:42Z saladj $
 * @since      1.0
 */
class AmnesiaController extends Zend_Controller_Action
{

    /**
     * Initialize object
     *
     * @return void
     */
    public function init()
    {
        // Proxy to flash messenger action helper
        parent::init();
        $this->_helper->layout->setLayout('auth');
        $this->flashMessenger = $this->_helper->flashMessenger;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        // Forward to another action
        $this->_forward('forgot-password');
    }

    /**
     * Forgot password action
     *
     * Intentionally left blank
     *
     * @return void
     */
    public function forgotPasswordAction()
    {

    }

    /**
     * Send password action
     *
     * @return void
     */
    public function sendPasswordAction()
    {
        // Get the email param passed in via the request
        $email = $this->_request->getParam('email');

        if (isset($email)) {
            // Send login details
            $this->_sendPasswordResetDetails($email);
        }
    }

    /**
     * Send login details to the user
     *
     * @param string $email the user's email
     *
     * @return void
     */
    protected function _sendPasswordResetDetails($email)
    {
        $modelUsers = new Application_Model_User();
        $user       = $modelUsers->getByEmail($email);

        if (!$user) {
            // Provide feedback to the user
            $this->flashMessenger->addMessage(array('warning' => "Your email wasn't recognised, did you spell it right?"));

            // Redirect back to forgot password page
            $url = $this->view->url(array('module' => 'default', 'controller' => 'amnesia', 'action' => 'forgot-password'), null, true);
            $this->_redirect($url, array('prependBase' => false));
        } else {

            // Create a password reset key
            $passwordResetKey = $modelUsers->createPasswordResetKey($email);

            // Dynamically get the components of the application URL
            $httpHost = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
            $appUrl   = $httpHost . $this->getRequest()->getBaseUrl();

            // Password reset url
            $actionUrl        = $this->view->url(array('module' => 'default','controller' => 'amnesia','action' => 'reset-password','key' => $passwordResetKey,'login' => $user['username']), null, true);
            $passwordResetUrl = $httpHost . $actionUrl;

            // Assign to view
            // @todo Get the app name dynamically
            $config = Zend_Registry::get('config');

            // Send Password Reset Email
            $passwordResetEmail = new App_Mail_PasswordResetEmail();
            $passwordResetEmail->setSenderWebsite($appUrl)
                ->setPasswordResetUrl($passwordResetUrl)
                ->setCustomerUserName($user['username'])
                ->setReceiver($email)
                ->setView($this->view);

            if ($passwordResetEmail->sendMail()) {
                // Provide feedback to the user on the next screen
                $this->flashMessenger->addMessage(array('highlight' => 'Instructions for resetting your password have been emailed to the email address we have stored for you.'));

                // Redirect back to login page
                $url = $this->view->url(array('module' => 'default','controller' => 'auth','action' => 'login'), null, true);
                $this->_redirect($url, array('prependBase' => false));
            } else { exit('error sending email');
                // Provide feedback to the user
                $this->flashMessenger->addMessage(array('warning' => "This account is not active."));

                // Redirect back to forgot page
                $url = $this->view->url(array('module' => 'default', 'controller' => 'amnesia'), null, true);
                $this->_redirect($url, array('prependBase' => false));
            }
        }
    }

    /**
     * Reset password action
     *
     * @return void
     */
    public function resetPasswordAction()
    {
        // Get the email and key params passed in via the request
        $username = $this->_request->getParam('login');
        $key = $this->_request->getParam('key');

       // exit('stop');

        // Get the user which has the passed email and password reset key
        $customerModel = new Application_Model_User();
        $user          = $customerModel->getByPasswordResetKey($username, $key);

        if (!$user) {

            // Provide feedback to the user
            $this->flashMessenger->addMessage(array('warning' => 'Sorry, that key does not appear to be valid.'));

            // Redirect back to forgot password page
            $url = $this->view->url(array('module' => 'default', 'controller' => 'amnesia','action' => 'forgot-password'), null, true);
            $this->_redirect($url, array('prependBase' => false));
        } else {

            $this->view->key      = $key;
            $this->view->username = $username;
            $this->view->userId   = $user['id'];
            $this->view->form     = new Application_Form_ResetPassword();
        }
    }

    /**
     * Process reset password
     *
     * @return void
     */
    public function processResetPasswordAction()
    {
        $userObj  = new Application_Model_User();
        $key      = $this->_getParam('key');
        $username = $this->_getParam('login');

        $userArray = $userObj->getByPasswordResetKey($username, $key);

        $form     = new Application_Form_ResetPassword();
        $formData = $this->_request->getPost();
        $user     = $userObj->fetchRow("id = '" . $userArray['id'] . "'");
        if ($form->isValid($formData)) {
            $password                 = md5($user->salt . $form->getValue('password'));
            $passPhrase               = $form->getValue('pass_phrase_1') . $form->getValue('pass_phrase_2') . $form->getValue('pass_phrase_3');
            $hashedPassPhrase         = $userObj->hashPassPhrase($user->id, $passPhrase);
            $user->password           = $password;
            $user->pass_phrase        = $hashedPassPhrase;
            $user->password_reset_key = null;
            $user->save();

            $userObj->login($username, $form->getValue('password'));
            switch ($user->acl_group_id)
            {
                case App_Controller_Plugin_Acl::ACL_GROUP_ADMIN:
                case App_Controller_Plugin_Acl::ACL_GROUP_SUPERADMIN:
                    $this->_helper->redirector('list', 'audio-job');
                case 2: // staff
                    $this->_helper->redirector('list-typist', 'audio-job');
                    break;
                case 3: // client
                    $this->_helper->redirector('list-client', 'job');
                    break;
            }
        } else {
            if ($form->getElement('pass_phrase_1')->hasErrors() || $form->getElement('pass_phrase_2')->hasErrors() || $form->getElement('pass_phrase_3')->hasErrors())
            {
                $this->view->passPhraseErrors = 'Please enter a valid passphrase';
            }

            $this->_helper->FlashMessenger(array('error' => 'There are errors. Please see item(s) in red below.'));

            $this->view->form     = $form;
            $this->view->username = $username;
            $this->view->key      = $key;
            $this->render('reset-password');
        }
    }

    /**
    * Set up the auth adapater for interaction with the database
    *
    * @param array $data - an array of Zend Form data
    * @return Zend_Auth_Adapter_DbTable
    */
    protected function _getAuthAdapter($username, $password)
    {
    	$dbAdapter = Zend_Registry::get('db');
    	$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
    	$authAdapter->setTableName('users')
    		->setIdentityColumn('username')
    		->setCredentialColumn('password')
            ->setCredentialTreatment('MD5(CONCAT(salt, ?))');

    	$authAdapter->setIdentity($username);
    	$authAdapter->setCredential($password);

    	return $authAdapter;
    }
}
