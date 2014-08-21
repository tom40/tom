<?php

class m121011_143929_grant_user_shiftbooker_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 51,
            'controller' => 'user-shift-booker',
            'action'     => 'index',
            'object'     => 'Application_Model_UsersShiftMapper',
        ));

        $this->insert('acl_privileges', array(
            'id'         => 52,
            'controller' => 'user-shift-booker',
            'action'     => 'ajax-fetch-shifts',
            'object'     => 'Application_Model_UsersShiftMapper',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 51, 'mode' => 'allow'));
        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 52, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 51));
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 52));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 51));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 52));
    }
}