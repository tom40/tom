<?php

/**
 * @see Zend_Db_Table
 */
require_once 'Zend/Db/Table.php';



/**
 * Class for SQL table interface.
 *
 * @category   App
 * @package    App_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2012 Take Note
 */
class App_Db_Table extends Zend_Db_Table
{

    /**
     * ACL object
     * @var App_Controller_Plugin_Acl
     */
    protected static $_acl;

    /**
     * Whether a shadow table exists
     *
     * @var bool
     */
    protected $_shadowExists = true;

    protected $_useAcl = false;


    /**
     * Set acl object
     *
     * @param App_Controller_Plugin_Acl $acl Acl object
     *
     * @return void
     */
    public static function setAclPlugin(App_Controller_Plugin_Acl $acl)
    {
        self::$_acl = $acl;
    }

    /**
     * Save the data to the database
     *
     * @param array $data
     * @param bool  $updateAcl OPTIONAL
     * @return int
     */
    public function save($data, $updateAcl = true)
    {
        $_debug = false;
        if ($_debug) echo "<p>save($data)</p>";

        $db = $this->getAdapter();
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);

        // Get and assign a transaction ID
        $db->beginTransaction();

        // Create Acl Role ID for the new record
        // If the array key exists, then mean we need to create an Acl Resource
        // ID. This needs to be done before we can save the object
        switch (get_class($this)) {
        	case 'Application_Model_UserMapper':
	        	if (!isset($data['id']) || empty($data['id'])) {

	                $modelAclRoles = new Application_Model_AclRoleMapper();
	                $modelAclRole = $modelAclRoles->createRow();
	                $acl_role_id = $modelAclRole->save();

	                // Assign the Acl Resource ID to the object's data array
	                $data['acl_role_id'] = $acl_role_id;
	          	}
	          	break;
    	}

		// Create Acl Resource ID for the new record
		// If the array key exists, then mean we need to create an Acl Resource
		// ID. This needs to be done before we can save the object
        switch (get_class($this)) {
        	case 'Application_Model_JobMapper':
        	case 'Application_Model_AudioJobMapper':
	            if (!isset($data['id']) || empty($data['id']))
                { // we're adding a new record
	                $modelAclResources = new Application_Model_AclResourceMapper();
	                $modelAclResource = $modelAclResources->createRow();
	                $acl_resource_id = $modelAclResource->save();

	                // Assign the Acl Resource ID to the object's data array
	                $data['acl_resource_id'] = $acl_resource_id;
	            }
	            break;
        }


        // If the id is set, but null or empty, then unset
        if (isset($data['id']) && (is_null($data['id']) || empty($data['id'])) ) {
            unset($data['id']);
        }

        // If the id is set and not empty
        if (isset($data['id']) && !empty($data['id'])) {

            /* UPDATE */
            $id    = $data['id'];
            $where = $db->quoteInto('id = ?', $id);
            $rowsUpdated = $this->update($data, $where);

            // Save a shadow record if the shadow table exists and rows were updated
            if ($this->_shadowExists && $rowsUpdated > 0) {
                $this->_saveShadow($id, 'update', $data);
            }

        } else {

            /* Insert */

            // Update meta information
            if (!isset($data['created_date'])) {
                $data['created_date'] = date('Y-m-d H:i:s');
            }

            if (!isset($data['created_user_id'])) {
                $data['created_user_id'] = $this->_getCurrentUserId();
            }

            $id = $this->insert($data);
            $data['id'] = $id;

            // Save a shadow record if the shadow table exists
            if ($this->_shadowExists) {
                $this->_saveShadow($id, 'insert', $data);
            }

            switch (get_class($this)) {
            	case 'Application_Model_JobMapper':
            		// add current user to acl for this object
            		$aclResourceId = $data['acl_resource_id'];
            		$aclRoleId = $this->_getCurrentUserAclRoleId();
            		$aclGroupId = $this->_getCurrentUserAclGroupId();
					$this->_insertAcl($aclResourceId, $aclRoleId, $aclGroupId);

					// add primary user for job (if different from user creating the object)
					$mapper = new Application_Model_UserMapper;

					if ($user = $mapper->fetchById($data['primary_user_id'])) {
						$aclRoleId = $user['acl_role_id'];
						$aclGroupId = $user['acl_group_id'];
					} else {
						throw new Exception('No user matching supplier id could be found');
					}

					$this->_insertAcl($aclResourceId, $aclRoleId, $aclGroupId);

            		break;
            	case 'Application_Model_AudioJobMapper':
            			// we're adding a new record
            		$aclResourceId = $data['acl_resource_id'];
            		$aclRoleId = $this->_getCurrentUserAclRoleId();
            		$aclGroupId = $this->_getCurrentUserAclGroupId();
					$this->_insertAcl($aclResourceId, $aclRoleId, $aclGroupId);

					// add primary user for job (if different from user creating the object).
					// get parent job id
					$mapper = new Application_Model_AudioJobMapper;
					$primaryUserId = $mapper->fetchParentJobPrimaryUserId($data['id']);

					$mapper = new Application_Model_UserMapper;

					if ($user = $mapper->fetchById($primaryUserId)) {
						$aclRoleId = $user['acl_role_id'];
						$aclGroupId = $user['acl_group_id'];
					} else {
						throw new Exception('No user matching supplier id could be found');
					}

					$this->_insertAcl($aclResourceId, $aclRoleId, $aclGroupId);

            		break;
            }

        }

        // Commit the transaction
        $db->commit();

        // Return the id of the saved record
        return $id;
    }

    /**
    * Delete data from the database
    *
    * @param int $id element id
    *
    * @return int
    */
    public function deleteById($id)
    {
    	$_debug = false;
    	if ($_debug) echo "<p>save($id)</p>";

    	$db = $this->getAdapter();
    	$db->setFetchMode(Zend_Db::FETCH_ASSOC);

    	// Get and assign a transaction ID
    	$db->beginTransaction();

    	// Save a shadow record if the shadow table exists and rows were updated
    	if ($this->_shadowExists) {
    		$this->_saveShadow($id, 'delete', array());
    	}

    	$this->delete('id = ' . $id);

    	// Commit the transaction
    	$db->commit();

    	// Return the id of the saved record
    	return true;
    }

    /**
     * Save a copy of the data to the shadow table, with optional notes against the transaction.
     *
     * @param int    $id
     * @param string $actionType
     * @param array  $data
     * @param string $notes      OPTIONAL
     * @return void
     */
    protected function _saveShadow($id, $actionType, $data, $notes = null)
    {
        //
        // TODO
        //
        // Handle case where no id has been passed but an UPDATE is being performed.
        //
        $db = $this->getAdapter();
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);

        // Get and assign a transaction ID
        $db->beginTransaction($notes);
        $transaction_id = $db->getTransactionId();

        // Add additional feilds to data array that are used in the shadow table
        $data['transaction_id'] = $transaction_id;
        $data['action_type']    = $actionType;

        // Ensure we have a full set the data for the shadow table by selecting the current state
        // of the data from the database and then 'overwriting' those fields that have been passed
        // in as part of the update
        $select = $db->select();
        $select->from($this->_name);
        $select->where('id = ?', $id);
        $results = $db->fetchRow($select);

        // Overwrite the current data with that which has been passed in
        $newData = array_merge($results, $data);

        // Load the shadow model and do an insert
        $shadow = $this->_loadShadowModel();
        $shadow->insert($newData);

        // Commit the transaction. (This actually decrements the nested transaction counter and
        // only actually performs a commit if the counter is equal to 1.)
        $db->commit();
    }

    /**
     * Load and return an instantiate shadow model object.
     *
     * @return Zend_Db_Table
     */
    protected function _loadShadowModel()
    {
        return new App_Db_Table($this->_name . '_shadow');
    }

    /**
    * Get the ID of the currently logged-in user
    *
    * @return int
    */
    protected function _getCurrentUserId()
    {
    	// User identity
    	if (Zend_Auth::getInstance()->hasIdentity()) {
    		return (int) Zend_Auth::getInstance()->getIdentity()->id;
    	} else {
    		throw new Exception('No current user could be found');
    	}
    }

    /**
    * Get the acl_role_id of the currently logged-in user
    *
    * @return int
    */
    protected function _getCurrentUserAclRoleId()
    {
    	// User identity
    	if (Zend_Auth::getInstance()->hasIdentity()) {
    		return (int) Zend_Auth::getInstance()->getIdentity()->acl_role_id;
    	} else {
    		throw new Exception('No current user could be found');
    	}
    }

    /**
    * Get the acl_role_id of the currently logged-in user
    *
    * @return int
    */
    protected function _getCurrentUserAclGroupId()
    {
    	// User identity
    	if (Zend_Auth::getInstance()->hasIdentity()) {
    		return (int) Zend_Auth::getInstance()->getIdentity()->acl_group_id;
    	} else {
    		throw new Exception('No current user could be found');
    	}
    }

    protected function _insertAcl($aclResourceId, $aclRoleId, $aclGroupId)
    {
    	$privilegeMapper = new Application_Model_AclGroupPrivilegeMapper();
		$privileges = $privilegeMapper->fetchByAclGroupIdAndObject($aclGroupId, get_class($this));

		$aclMapper = new Application_Model_AclMapper();

		foreach ($privileges as $privilege) {
	    	$aclData = array(
	    		'role_id'      => $aclRoleId,
	    		'resource_id'  => $aclResourceId,
	    		'privilege_id' => $privilege['privilege_id'],
	    		'mode'         => 'allow'
	    	);
	    	$aclMapper->insert($aclData);
		}
    }

    /**
     * Short cut to quote into method
     *
     * @param string  $text  The text with a placeholder.
     * @param mixed   $value The value to quote.
     * @param string  $type  OPTIONAL SQL datatype
     * @param integer $count OPTIONAL count of placeholders to replace
     *
     * @return string An SQL-safe quoted value placed into the original text.
     */
    protected function _quoteInto( $text, $value, $type = null, $count = null )
    {
        return $this->getAdapter()->quoteInto( $text, $value, $type, $count );
    }

}
