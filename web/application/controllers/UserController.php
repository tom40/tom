<?php

class UserController extends App_Controller_Action
{

public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
    }

    public function activationAction()
    {
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$id = $this->_request->getParam('id');

    	if (!empty($id)) {
    		$userMapper  = new Application_Model_UserMapper();
    		$data = array();
    		$data['id'] = $id;
    		$data['active'] = 1;
    		$id = $userMapper->save($data);

    		$data = $userMapper->fetchById($id);
    		$this->view->data = $data;

    		$output['status'] = 'ok';
    		$output['msg'] = $this->view->render('user/list-user-actions-cell.phtml');
    	} else {
    		$output['status'] = 'fail';
    	}

    	echo json_encode($output);

    }

    public function deactivationAction()
    {
    	$this->view->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$id = $this->_request->getParam('id');

    	if (!empty($id)) {
    		$userMapper  = new Application_Model_UserMapper();
    		$data = array();
    		$data['id'] = $id;
    		$data['active'] = 0;
    		$id = $userMapper->save($data);

    		$data = $userMapper->fetchById($id);
    		$this->view->data = $data;

    		$output['status'] = 'ok';
    		$output['msg'] = $this->view->render('user/list-user-actions-cell.phtml');
    	} else {
    		$output['status'] = 'fail';
    	}
    	echo json_encode($output);

    }

    public function listAction()
    {
    	// action body
    	$users = new Application_Model_UserMapper();
    	$this->view->users = $users->fetchAll();
    }

	public function editNotUsedAction()
    {
        // action body
    	$request = $this->getRequest();
    	$id = $this->_request->getParam('id');

    	$form = new Application_Form_UserEdit();

    	$userMapper = new Application_Model_UserMapper();

    	if ($this->getRequest()->isPost()) {
    		if ($form->isValid($request->getPost())) {
    			$data = $form->getValues();
    			if ($userMapper->save($data)) {
    				$this->_helper->FlashMessenger('Record Saved!');
    				return $this->_helper->redirector->gotoSimple('list', 'user', 'default');
    			} else {
    				$this->_helper->FlashMessenger(array('error' => 'Record Not Saved!'));
    			}
    		} else {
    			$this->_helper->FlashMessenger(array('error' => 'Form not correctly completed. Please see item(s) in red text below.'));
    		}
    	} else {
    		$data = $userMapper->fetchById($id);
    		$form->populate($data);
    	}
    	$this->view->form = $form;
    	$this->view->user = $data;

    }


    public function editAction()
    {
    	// action body
    	$request = $this->getRequest();
    	$id = $this->_request->getParam('id');

    	$form = new Application_Form_UserEdit($id);
        $form->init();
    	$typistForm = new Application_Form_TypistCreate();
    	$clientUserForm = new Application_Form_ClientUserEdit();

    	$userMapper = new Application_Model_UserMapper();
    	$typistMapper = new Application_Model_TypistMapper();
    	$proofreaderMapper = new Application_Model_ProofreaderMapper();
    	$clientUserMapper = new Application_Model_ClientsUserMapper();

    	if ($this->getRequest()->isPost()) {
    	// check all forms are valid since if not then need to send feedback on all forms at the same time
    		$formIsValid    = false;

            // No password updates if no password supplied
            $password = $this->_request->getParam('password');
            if (empty($password))
            {
                $form->getElement('password')->setRequired(false);
                $form->getElement('password_confirm')->setRequired(false);
            }

    		$formIsValid = $form->isValid($request->getPost());

    		$data = $form->getValues();

    		$subformData = array();
    		$subformIsValid = false;
    		if ($this->_acl->isAdmin($data['acl_group_id']))
            {
    			$subformIsValid = true;
    		}
            elseif ($data['acl_group_id'] == 2)
            {
    			$subformIsValid = $typistForm->isValid($request->getPost());
    			$subformData = $typistForm->getValues();
    		}
            elseif ($data['acl_group_id'] == 3)
            {
    			$subformIsValid = $clientUserForm->isValid($request->getPost());
    			$subformData = $clientUserForm->getValues();
    		}
            else
            {
    			// do nothing
    		}

    		if ($formIsValid && $subformIsValid)
            {
    			$data = $form->getValues();

                if (!empty($password))
                {
                    $data['salt']        = md5(time());
                    $data['password']    = md5($data['salt'] . $data['password']);
                    $data['pass_phrase'] = NULL;
                }
                else
                {
                    unset($data['password']);
                }

	    		//$data['acl_role_id'] = 1;
	    		unset($data['password_confirm']);
	    		$userId = $userMapper->save($data);
	    		if ($userId)
                {
	    			if ($this->_acl->isAdmin($data['acl_group_id']))
                    {
	    				$complete = true;
	    			}
                    else
                    {
	    				$subformData['user_id'] = $userId;
		    			if ($data['acl_group_id'] == 2)
                        {
		    				$origData = $subformData;
		    				if ($origData['is_typist'])
                            {
		    					// remove unwanted columns from form
		    					unset($subformData['is_typist']);
		    					unset($subformData['is_proofreader']);
		    					unset($subformData['proofreader_grade_id']);
		    					unset($subformData['proofreading_comments']);
		    					unset($subformData['typist_id']);
		    					unset($subformData['proofreader_id']);

		    					$subformData['id'] = $origData['typist_id'];

		    					$subformData['grade_id'] = $origData['typist_grade_id'];
                                $subformData['payrate_id'] = $origData['typist_payrate_id'];
		    					unset($subformData['typist_grade_id'], $subformData['typist_payrate_id']);

		    					$subformMapper = new Application_Model_TypistMapper();

		    					$typistComplete = $subformMapper->save($subformData);
		    				}
                            else
                            {
		    					// set default complete value (Ok to set to 1 if no typist - just means save either successful or not required
		    					$typistComplete = 1;
		    				}

		    				if ($origData['is_proofreader'])
                            {
		    					// only a few columns required for audio_jobs_proofreaders so create new array
		    					$subformData = array();
		    					$subformData['id'] = $origData['proofreader_id'];

		    					$subformData['user_id'] = $userId;
		    					$subformData['grade_id'] = $origData['proofreader_grade_id'];
		    					$subformData['proofreading_comments'] = $origData['proofreading_comments'];

		    					$subformMapper = new Application_Model_ProofreaderMapper();

		    					$proofreaderComplete = $subformMapper->save($subformData);
		    				}
                            else
                            {
		    					// set default complete value (Ok to set to 1 if no typist - just means save either successful or not required
		    					$proofreaderComplete = 1;
		    				}

		    				if ($typistComplete && $proofreaderComplete)
                            {
		    					$complete = 1;
		    				}
                            else
                            {
		    					$complete = 0;
		    				}

		    			}
                        elseif ($data['acl_group_id'] == 3)
                        {
                            $complete = true;
		    			}
	    			}
	    		}

	    		if ($complete)
                {
	    			$this->_helper->FlashMessenger('Record Saved!');
                    //$this->_sendUserUpdateNotification($userId, $password);
                    $url = $this->view->url(array('module' => 'default', 'controller' => 'user', 'action' => 'edit', 'id' => $id), null, true);
                    $this->_redirect($url, array('prependBase' => false));
	    		}
                else
                {
	    			$this->_helper->FlashMessenger(array('error' => 'Record Not Saved!'));
				}

    		} else {
    			$this->_helper->FlashMessenger(array('error' => 'Form not correctly completed. Please see item(s) in red text below.'));
    		}
    	} else {
    		$data = $userMapper->fetchById($id);
    		$form->populate($data);

    		$typistData = $typistMapper->fetchByUserId($id);
    		if($typistData) {
    			$typistData['typist_id'] = $typistData['id'];
    			$typistData['is_typist'] = 1;
    			$typistData['typist_grade_id'] = $typistData['grade_id'];
                $typistData['typist_payrate_id'] = $typistData['payrate_id'];
    		}

    		$proofreaderData = $proofreaderMapper->fetchByUserId($id);
    		if($proofreaderData) {
    			$typistData['is_proofreader'] = 1;
    			$typistData['proofreader_id'] = $proofreaderData['id'];
    			// rename the proofreader grade id or else this is lost when arrays merged below since there is also a grade_id field for typists
    			$typistData['proofreader_grade_id'] = $proofreaderData['grade_id'];

    			$typistData = array_merge($typistData, $proofreaderData);

    		}

    		if (isset($typistData['is_typist']) || isset($typistData['is_proofreader'])) {

	    		$typistForm->populate($typistData);

	    		// set defaults for select elements
	    		if (isset($typistData['is_typist'])) {
	    			$typistForm->setDefault('typist_grade_id', $typistData['typist_grade_id']);
	    		}
	    		if (isset($typistData['is_proofreader'])) {
	    			$typistForm->setDefault('proofreader_grade_id', $typistData['proofreader_grade_id']);
	    		}
    		}

    		$clientUserData = $clientUserMapper->fetchByUserId($id);
    		if($clientUserData) {
    			$clientUserForm->populate($clientUserData);
    		}
    	}
    	$this->view->form = $form;

    	if (isset($typistForm)) {
    		$this->view->typistForm = $typistForm;
    	}

    	if (isset($clientUserForm)) {
    		$this->view->clientUserForm = $clientUserForm;
    	}

    	$this->view->user = $data;

    	$this->render('edit');
    }

    public function createAction()
    {
    	// action body
    	$request = $this->getRequest();
    	$id = $this->_request->getParam('id');

    	$form = new Application_Form_UserCreate();
    	$typistForm = new Application_Form_TypistCreate();
    	$clientUserForm = new Application_Form_ClientUserCreate();

    	$userMapper = new Application_Model_UserMapper();

    	if ($this->getRequest()->isPost())
        {

    		// check all forms are valid since if not then need to send feedback on all forms at the same time
    		$formIsValid = false;
    		$formIsValid = $form->isValid($request->getPost());

    		$data = $form->getValues();

    		$subformData = array();
    		$subformIsValid = false;
    		if ($this->_acl->isAdmin($data['acl_group_id']))
            {
    			$subformIsValid = true;
    		}
            elseif ($data['acl_group_id'] == 2)
            {
    			$subformIsValid = $typistForm->isValid($request->getPost());
    			$subformData = $typistForm->getValues();
    		}
            elseif ($data['acl_group_id'] == 3)
            {
    			$subformIsValid = $clientUserForm->isValid($request->getPost());
    			$subformData = $clientUserForm->getValues();
    		}
            else
            {
    			// do nothing
    		}

    		if ($formIsValid && $subformIsValid)
            {
    			$data = $form->getValues();

	    		$data['acl_role_id'] = 1;
	    		unset($data['password_confirm']);

                // Generate password
                $data['salt']     = md5(time());
                $data['password'] = md5($data['salt'] . $data['password']);
	    		$userId           = $userMapper->save($data);

	    		if ($userId)
                {
	    			if ($this->_acl->isAdmin($data['acl_group_id']))
                    {
	    				$complete = true;
	    			}
                    else
                    {
	    				$subformData['user_id'] = $userId;
		    			if ($data['acl_group_id'] == 2)
                        {
		    				$origData = $subformData;
		    				if ($origData['is_typist'])
                            {
		    					// remove unwanted columns from form
		    					unset($subformData['is_typist']);
		    					unset($subformData['typist_id']);
		    					unset($subformData['is_proofreader']);
		    					unset($subformData['proofreader_id']);
		    					unset($subformData['proofreader_grade_id']);
		    					unset($subformData['proofreading_comments']);

		    					$subformData['grade_id'] = $subformData['typist_grade_id'];
                                $subformData['payrate_id'] = $origData['typist_payrate_id'];
                                unset($subformData['typist_grade_id'], $subformData['typist_payrate_id']);

		    					$subformMapper = new Application_Model_TypistMapper();
		    					$typistComplete = $subformMapper->save($subformData);
		    				}
                            else
                            {
		    					// set default complete value (Ok to set to 1 if no typist - just means save either successful or not required
		    					$typistComplete = 1;
		    				}

		    				if ($origData['is_proofreader'])
                            {
		    					// only a few columns required for audio_jobs_proofreaders so create new array
		    					$subformData = array();
		    					$subformData['user_id'] = $userId;
		    					$subformData['grade_id'] = $origData['proofreader_grade_id'];
		    					$subformData['proofreading_comments'] = $origData['proofreading_comments'];

		    					$subformMapper = new Application_Model_ProofreaderMapper();
		    					$proofreaderComplete = $subformMapper->save($subformData);
		    				}
                            else
                            {
		    					// set default complete value (Ok to set to 1 if no typist - just means save either successful or not required
		    					$proofreaderComplete = 1;
		    				}

		    				if ($typistComplete && $proofreaderComplete)
                            {
		    					$complete = 1;
		    				}
                            else
                            {
		    					$complete = 0;
		    				}

		    			}
                        elseif ($data['acl_group_id'] == 3)
                        {
		    				$subformMapper = new Application_Model_ClientsUserMapper();
		    				$complete = $subformMapper->save($subformData);
		    			}
	    			}
	    		}

	    		if ($complete)
                {
	    			$this->_helper->FlashMessenger('Record Saved!');
                    $this->_sendUserCreateNotification($userId, $form->getValue('password'));
	    			return $this->_helper->redirector->gotoSimple('list', 'user', 'default');
	    		}
                else
                {
	    			$this->_helper->FlashMessenger(array('error' => 'Record Not Saved!'));
				}

    		}
            else
            {
                // If password is correct populate the form password fields
                $passwordErrors        = $form->getErrors('password');
                $passwordConfirmErrors = $form->getErrors('password_confirm');
                if (empty($passwordErrors) && empty($passwordConfirmErrors))
                {
                    $form->password->renderPassword         = true;
                    $form->password_confirm->renderPassword = true;
                }

    			$this->_helper->FlashMessenger(array('error' => 'Form not correctly completed. Please see item(s) in red text below.'));
    		}
    	}
    	$this->view->form           = $form;
    	$this->view->typistForm     = $typistForm;
    	$this->view->clientUserForm = $clientUserForm;
    }

    /**
     * Send email to user that their account is updated
     *
     * @param int    $userId
     * @param string $password
     *
     * @return void
     */
    protected function _sendUserUpdateNotification($userId, $newPassword = null)
    {
        $userMail = $this->_setUserNotificationDefaults($userId, new App_Mail_UpdateUserEmail(), false);
        if (!empty($newPassword))
        {
            $userMail->setPassword($newPassword);
        }
        $userMail->sendMail();
    }

    /**
     * Send newly created login details to user
     *
     * @param int    $userId   the user id
     * @param string $password the user's password
     *
     * @return void
     */
    protected function _sendUserCreateNotification($userId, $newPassword)
    {
        $userMail = $this->_setUserNotificationDefaults($userId, new App_Mail_NewUserEmail());
        $userMail->setPassword($newPassword)
                 ->sendMail();
    }

    /**
     * Sets user email notification defaults shared by all user emails
     *
     * @param int           $userId   the user id
     * @param App_Mail_Mail $userMail the user email obj
     *
     * @return App_Mail_Mail
     */
    protected function _setUserNotificationDefaults($userId, $userMail, $includeReceiver = true)
    {
        // Dynamically get the components of the application URL
        $httpHost = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
        $appUrl   = $httpHost . $this->getRequest()->getBaseUrl();

        $userMapper = new Application_Model_UserMapper();
        $userData   = $userMapper->fetchById($userId);
        $userMail->setName($userData['name'])
             ->setUsername($userData['username']);

        if ($includeReceiver)
        {
            $userMail->setReceiver($userData['email']);
        }
        else
        {
            $config = Zend_Registry::get('config');
            $userMail->setReceiver($config->app->email->systemEmail);
        }

        $userMail->setSiteUrl($appUrl)
                 ->setView($this->view);
        return $userMail;
    }
}

