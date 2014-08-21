<?php

class Application_Model_User extends App_Db_Table
{
    const TYPIST_USER      = 'typist';
    const PROOFREADER_USER = 'proofreader';

	protected $_name = 'users';

    /**
     * Fetch a user by their email address
     *
     * @param string $email the user email
     *
     * @return User
     */
	public function getByEmail($email)
    {
		$db = $this->getAdapter();
        $select = $db->select();
        $select->from(array('u' => $this->_name))
            ->where('email = ?', $email);

		$result = $db->fetchRow($select);

		return $result;
    }

    /**
     * Fetch emails of given users
     *
     * @param array $userIds list of users
     *
     * @return User
     */
	public function getUserEmailList($userIds, $excludeUserId)
    {
        if (!empty($userIds))
        {
            $db = $this->getAdapter();
            $userIds = implode(',', $userIds);
            $sql = "SELECT group_concat(email) FROM {$this->_name} WHERE id IN ({$userIds}) AND id != {$excludeUserId}";
            $res = $db->fetchOne($sql);
            return $res;
        }

		return null;
    }

    /**
     * Fetch a user by their username
     *
     * @param string $username the user name
     *
     * @return User
     */
	public function getByUsername($username)
    {
		$db = $this->getAdapter();

        $sql = "SELECT
          `u`.*,
            CASE WHEN p.id IS NOT NULL
              THEN 1
              ELSE 0
            END AS is_proofreader,
            CASE WHEN t.id IS NOT NULL
              THEN 1
              ELSE 0
            END AS is_typist
            FROM `users` AS `u`
            left join typists t on u.id = t.user_id
            left join proofreaders p on u.id = p.user_id
            WHERE " . $db->quoteInto( 'username = ?', $username );

		$result = $db->fetchRow($sql);

		return $result;
    }

    /**
     * Fetch a user based on password reset key
     *
     * @param string $username    the username
     * @param string $resetKey the password reset key
     *
     * @return User
     */
	public function getByPasswordResetKey($username, $resetKey)
    {
		$db = $this->getAdapter();
        $select = $db->select();
        $select->from(array('u' => $this->_name))
            ->where('username = ?', $username)
            ->where('password_reset_key = ?', $resetKey);

		$result = $db->fetchRow($select);

		return $result;
    }

    /**
     * Create a password reset key
     *
     * @param string  $email the user email
     *
     * @return string
     */
    public function createPasswordResetKey($email)
    {
        $db = $this->getAdapter();

        // Create password reset key
        $key = md5($email . mt_rand());

        $data = array(
            'password_reset_key' => $key,
            'pass_phrase'       => ''
        );

        $where = array(
            $db->quoteInto('email = ?', $email)
        );

        $rowsUpdated = $this->update($data, $where);

        return $key;
    }

    /**
     * Creates, saves and returns a new random password for a user.
     *
     * @param int $userId
     * @return string
     */
    public function createPassword($userId)
    {
        $db = $this->getAdapter();

        // Create new salt by hashing a random value
        $salt = $this->getSaltByUserId($userId);

        if (empty($salt)) {
            $salt = md5(time());
        }

        // Create plain text password, then encrypt using the above salt
        $plainTextPassword = $this->_generatePassword();
        $password          = md5($salt . $plainTextPassword);

        // Create data array
        $data = array(
            'salt'               => $salt,
            'password'           => $password,
            'password_reset_key' => null,
        );

        // Contruct WHERE clause to filter to the correct user
        $where = array(
            $db->quoteInto('id = ?', $userId)
        );

        // Update the database
        if (!$this->update($data, $where))
        {
            throw new Exception('Could not update password for user');
        }

        // Return the plain text password
        return $plainTextPassword;
    }

    /**
     * Hash pass phrase using the salt
     *
     * @param int $userId                 the user to hash the pass phrase for
     * @param string $plainTextPassPhrase the plain text pass phrase
     *
     * @return bool
     */
    public function hashPassPhrase($userId, $plainTextPassPhrase)
    {
        $salt       = $this->getSaltByUserId($userId);
        $passPhrase = md5($salt . $plainTextPassPhrase);
        //echo 'passphrase: ' . $passPhrase;exit;
        return $passPhrase;
    }


    /**
     * Get the salt value for a given user.
     *
     * @param int $userid
     * @return string
     */
    public function getSaltByUserId($userId)
    {
        $db = $this->getAdapter();
        $select = $db->select();

        $select->from($this->_name, 'salt');
        $select->where('id = ?', $userId);

        $result = $db->fetchOne($select);
        return $result;
    }

    /**
     * Create and return a new password.
     * Extract from OneResult_Password::create
     *
     * @param int $length OPTIONAL
     * @param int $type   OPTIONAL Type can be one of:
     *
     * @return string
     */
    protected function _generatePassword($length = 8)
    {
        $ranges = '65-90,97-122,48-57';

        // Initialise string for password
        $p = '';

        if ($ranges != '') {
            $range = explode(',', $ranges);
            $rangesCount = count($range);
            for ($i = 1; $i <= $length; $i++) {
                $r = mt_rand(0, $rangesCount-1);
                list($min, $max) = explode('-', $range[$r]);
                $p .= chr(mt_rand($min, $max));
            }
        }

        // Return the new password
        return $p;
    }

    /**
     * Logs the user into the website
     *
     * @param type $login_parameters
     */
    public function login($username, $password)
    {
        $result = false;

        $authAdapter  = $this->setupAuthAdapter($username, $password);
        $authInstance = Zend_Auth::getInstance();
        $authResult   = $authInstance->authenticate($authAdapter);

        if ($authResult->isValid())
        {
            $result = true;
            $this->storeAuthSession($authAdapter, $authInstance);
        }

        return $result;
    }

    /**
     * Stores the users session data as pulled from the database
     * @param type $authAdapter The setup authAdapter used to authenticate
     * @param type $authInstance The authentication instance
     */
    private function storeAuthSession($authAdapter, $authInstance)
    {
        // omit salt and password from saved storage data
        $authInstance->getStorage()->write($authAdapter->getResultRowObject(null, array('salt', 'password')));
    }

    /**
     * Setup an auth adapter to authenticate a user against the database
     *
     * @return Zend_Auth_Adapter_DbTable An instance setup and ready to be authenticated with
     */
    private function setupAuthAdapter($username, $password)
    {
        $adapter = new Zend_Auth_Adapter_DbTable($this->getDefaultAdapter());

        $adapter->setTableName('users')
                ->setIdentityColumn('username')
                ->setCredentialColumn('password')
                ->setCredentialTreatment("MD5(CONCAT(salt, ?))")
                ->setIdentity($username)
                ->setCredential($password);

        return $adapter;
    }
}

