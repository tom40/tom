<?php

class m121010_132804_grant_user_acl_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 50,
            'controller' => 'index',
            'action'     => 'index',
            'object'     => 'Application_Model_JobMapper',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 50, 'mode' => 'allow'));
        $this->insert('acl_group_privileges', array( 'group_id' => 3, 'privilege_id' => 50, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 50));
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => 50));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 50));
    }
}