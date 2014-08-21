<?php

class Application_Model_ClientMapper extends App_Db_Table
{
	protected $_name = 'clients';

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_Client_Row';
	
	public function fetchById($id)
	{
		$db = $this->getAdapter();
	
		$select = $db->select();
		$select->from(array('c' => 'clients'));
		$select->where('id = ?', $id);
		$results = $db->fetchRow($select);
		return $results;
	}
	
	public function fetchAllForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();
	
		// staff should never see client names
		if ($this->_getCurrentUserAclGroupId() == 2) {
			return;
		} 
		
		$select->from(array('c' => 'clients'), array('key' =>'id', 'value' => 'name'));
		if ($this->_getCurrentUserAclGroupId() == 3) {
			$select->joinLeft(array('cu' => 'clients_users'), 'cu.client_id = c.id', array());
			$select->where('cu.user_id = ?', $this->_getCurrentUserId());
		}
		$select->order('name');
	
		$results = $db->fetchAll($select);
		return $results;
	}
}

