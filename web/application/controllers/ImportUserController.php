<?php

class ImportUserController extends App_Controller_Action
{
    const USER_TYPE_STAFF       = 'staff';
    const USER_TYPE_CLIENT      = 'client';
    const ERROR_EMPTY_USER_TYPE = 'Please select a user type';
    const ERROR_NO_FILE         = 'Please select a valid CSV file';

    public function init()
    {
        /* Initialize action controller here */
        $this->flashMessenger = $this->_helper->flashMessenger;
    }

    public function indexAction()
    {

    }

    /**
     *
     */
    public function processAction()
    {
        $request = $this->getRequest();

        if ($request->isPost())
        {
            $formData = $this->getRequest()->getPost();

            if (!isset($formData['user_type']))
            {
                $this->flashMessenger->addMessage(array('error' => self::ERROR_EMPTY_USER_TYPE));
            }
            else
            {
                $userType             = $formData['user_type'];
                $this->view->userType = $userType;
                if ( isset($_FILES["file"]))
                {
                    if ($_FILES["file"]["error"] > 0) {
                        $this->flashMessenger->addMessage(array('error' => self::ERROR_NO_FILE));
                    }
                    else
                    {
                        $tempFilename = $_FILES["file"]["tmp_name"];
                        $data         = $this->_csvToArray($tempFilename);

                        if (self::USER_TYPE_CLIENT === $userType)
                        {
                            $this->_processClients($data);
                        }
                        elseif (self::USER_TYPE_STAFF === $userType)
                        {
                            $this->_processStaff($data);
                        }
                    }
                }
                else
                {
                    $this->flashMessenger->addMessage(array('error' => self::ERROR_NO_FILE));
                }
            }
        }

        $this->render('index');
    }

    /**
     * Process client users
     *
     * @return void
     */
    protected function _processClients($data)
    {
        $userMapper       = new Application_Model_UserMapper();
    	$clientUserMapper = new Application_Model_ClientsUserMapper();

        foreach ($data as $user)
        {
            $user['acl_group_id'] = '3';
            $clientId             = $user['client_id'];
            $password         = $user['password'];
            $user['salt']     = md5(time());
            $user['password'] = md5($user['salt'] . $user['password']);

            unset($user['client_id']);
            $userId   = $userMapper->save($user);
            $this->_sendUserCreateNotification($userId, $password);

            $clientData = array(
                'client_id' => $clientId,
                'user_id'   => $userId
            );

            $clientUserMapper->save($clientData);
        }
    }

    /**
     * Process staff users
     *
     * @return void
     */
    protected function _processStaff($data)
    {
        //$data              = $this->_csvToArray();
        $userMapper        = new Application_Model_UserMapper();
    	$typistMapper      = new Application_Model_TypistMapper();
    	$proofreaderMapper = new Application_Model_ProofreaderMapper();

        $userKeys = array(
            'name',
            'username',
            'password',
            'email',
            'email_alternative',
            'landline',
            'mobile',
            'comments',
            'acl_group_id'
        );


        $typistGrades = array(
            'TRAINEE'            => 2,
            'TYPIST'             => 3,
            'SUPER TYPIST'       => 4,
            'SUPER DUPER TYPIST' => 5,
            'SUPER SUPER TYPIST' => 5
        );

        $proofReaderGrades = array(
            'PROOFREADER'             => 3,
            'SUPER DUPER PROOFREADER' => 5
        );
//var_dump($data); die;
        foreach ($data as $user)
        {
            $user     = array_map('trim', $user);
            $userData = array();

            foreach ($userKeys as $key)
            {
                $userData[$key] = $user[$key];
            }

            $password                 = $userData['password'];
            $userData['salt']         = md5(time());
            $userData['password']     = md5($userData['salt'] . $userData['password']);
            $userData['acl_group_id'] = 2;
            
            $userId = $userMapper->save($userData);
            $this->_sendUserCreateNotification($userId, $password);

            if ('1' === $user['is_typist'])
            {
                $typistGrade = $user['typist_grade_id'];
                if (isset($typistGrades[$typistGrade]))
                {
                    $typistGrade = $typistGrades[$typistGrade];
                }

                $typistData = array(
                    'user_id'           => $userId,
                    'grade_id'          => $typistGrade,
                    'typing_comments'   => $user['typing_comments'],
                    'trained_summaries' => $user['trained_summaries'],
                    'trained_notes'     => $user['trained_notes'],
                    'trained_legal'     => $user['trained_legal'],
                    'note_taker'        => $user['note_taker'],
                    'full'              => $user['full'],
                    'typing_speed'      => $user['typing_speed']
                );
                try
                {
                $typistMapper->save($typistData);
                }
                catch(Exception $exp)
                {
                    var_dump($user); die;
                }
            }
            if ('1' === $user['is_proofreader'])
            {
                $proofGrade = $user['proofreader_grade_id'];
                if (isset($proofReaderGrades[$proofGrade]))
                {
                    $proofGrade = $proofReaderGrades[$proofGrade];
                }

                $proofReaderData = array(
                    'user_id'               => $userId,
                    'grade_id'              => $proofGrade,
                    'proofreading_comments' => $user['proofreading_comments']
                );
                //var_dump($user); die;
                try {
                    $proofreaderMapper->save($proofReaderData);
                }
                catch (Exception $exp)
                {
                    var_dump($user); die;
                }
            }
        }
    }

    /**
     * Get array from CSV
     *
     * @return array
     */
    protected function _csvToArray($tempFilename)
    {
        $file          = fopen($tempFilename, 'r');
        $formattedData = array();

        if (!empty($file))
        {
            $headings = array();
            $data = array();
            $counter = 1;
            while (($line = fgetcsv($file)) !== FALSE)
            {
                if (1 == $counter)
                {
                     $headings = $line;
                }
                else
                {
                    $data[] = $line;
                }
                $counter++;
            }

            // Set the correct key's
            if (!empty($data))
            {
                foreach($data as $item)
                {
                    $formattedData[] = array_combine($headings, $item);
                }
            }
        }

        return $formattedData;
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
    protected function _setUserNotificationDefaults($userId, $userMail)
    {
        // Dynamically get the components of the application URL
        $httpHost = $this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost();
        $appUrl   = $httpHost . $this->getRequest()->getBaseUrl();

        $userMapper = new Application_Model_UserMapper();
        $userData   = $userMapper->fetchById($userId);
        $userMail->setName($userData['name'])
                 ->setUsername($userData['username'])
                 ->setReceiver($userData['email'])
                 ->setSiteUrl($appUrl)
                 ->setView($this->view);
        return $userMail;
    }

}