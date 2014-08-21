<?php

class AuthController extends App_Controller_Action
{

    public function init()
    {
        parent::init();
        $this->_helper->layout->setLayout('auth');
    }

    public function indexAction()
    {
        // action body
    	$url = $this->view->url(
    	array(
			'module'     => 'default',
    	    'controller' => 'auth',
    	    'action'     => 'login'
    	),
    	null, true);
    	$this->_redirect($url, array('prependBase' => false));
    }

    public function logoutAction()
    {
    	// action body
    	Zend_Auth::getInstance()->clearIdentity();
    	$url = $this->view->url(
    	array(
			'module'     => 'default',
    	    'controller' => 'auth',
    	    'action'     => 'login'
    	),
    	null, true);
    	$this->_redirect($url, array('prependBase' => false));
    }

    public function loginAction()
    {
    	// action body
    	$request = $this->getRequest();
    	$form    = new Application_Form_Login();

    	if ($this->getRequest()->isPost())
        {
            $formData = $this->_request->getPost();
            for ( $p = 1; $p < 4; $p++ )
            {
                $key            = 'pass_phrase_' . $p;
                $formData[$key] = trim( $formData[$key] );
            }

    		if ( $form->isValid( $formData ) )
            {
                $data     = $form->getValues();
                $username = $data['username'];
                $password = $data['password'];

    			// Do the authentication
    			$authAdapter = $this->_getAuthAdapter($username, $password);
    			$auth        = Zend_Auth::getInstance();
    			$result      = $auth->authenticate($authAdapter);

    			if (!$result->isValid())
                {
                    $this->_flash->addMessage(array('error' => 'Sorry, that username/password doesn\'t work.<br />Please remember that passwords are case sensitive.'));
    				$url = $this->view->url(
    				array(
						'module'     => 'default',
    			        'controller' => 'auth',
    			        'action'     => 'login'
    				),
    				null, true);
     				$this->_redirect($url, array('prependBase' => false));

    			}
                else
                {
                    $userModel        = new Application_Model_User();
                    $user             = $userModel->getByUsername($username);

                    if ( '1' == $user['phrase_opt_in'] )
                    {
                        $passPhrase       = $data['pass_phrase_1'] . $data['pass_phrase_2'] . $data['pass_phrase_3'];
                        $continueLogin    = true;
                        $hashedPassPhrase = $userModel->hashPassPhrase($user['id'], $passPhrase);

                        // If user pass phrase is empty set it
                        if (empty($user['pass_phrase']))
                        {
                            // Set pass phrase
                            $newData = array();
                            $newData['pass_phrase'] = $hashedPassPhrase;
                            $userModel->update($newData, "username = '$username'");
                            Zend_Auth::getInstance()->clearIdentity();
                        }
                        else
                        {
                            if ($hashedPassPhrase !== $user['pass_phrase'])
                            {
                                Zend_Auth::getInstance()->clearIdentity();
                                $this->_flash->addMessage(array('error' => 'Sorry, the passphrase entered is incorrect'));
                                $continueLogin = false;
                            }
                        }
                    }
                    else
                    {
                        $continueLogin = true;
                    }

                    if ($continueLogin)
                    {
                        // Success, so store database row to auth's storage
                        $data     = $authAdapter->getResultRowObject(null, array('password'));
                        $decorate = array( 'is_proofreader', 'is_typist' );

                        foreach ( $decorate as $field )
                        {
                            if ( isset( $user[$field] ) )
                            {
                                $data->{$field} = (bool) (int) $user[$field];
                            }
                        }

                        $auth->getStorage()->write( $data );

         				// Update the timeout
                        $authNamespace = new Zend_Session_Namespace('Zend_Auth');
                        $config = Zend_Registry::get('config');

                        switch ($data->acl_group_id) {
                            case App_Controller_Plugin_Acl::ACL_GROUP_ADMIN:
                            case App_Controller_Plugin_Acl::ACL_GROUP_SUPERADMIN:
                                $authNamespace->timeout = time() + 1209600;
                                $this->_helper->redirector('list', 'audio-job');
                            case 2: // staff
                                $authNamespace->timeout = time() + 7200;
                                $this->_helper->redirector('list-typist', 'audio-job');
                                break;
                            case 3: // client
                                $authNamespace->timeout = time() + 7200;
                                $this->_helper->redirector('list-client', 'job');
                                break;
                        }
                    }
    			}
    		}
            else
            {
                $form->populate($formData);
                $this->_flash->addMessage(array('error' => 'There are errors. Please see item(s) in red below.'));
    		}
    	}

    	$this->view->form = $form;
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
            ->setCredentialTreatment('MD5(CONCAT(salt, ?)) AND active = "1"');

    	$authAdapter->setIdentity($username);
    	$authAdapter->setCredential($password);

    	return $authAdapter;
    }

}

