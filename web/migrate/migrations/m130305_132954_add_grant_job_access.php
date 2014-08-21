<?php

class m130305_132954_add_grant_job_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 62,
            'controller' => 'client',
            'action'     => 'fetch-client-users',
            'object'     => 'Application_Model_ClientsUserMapper',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 3, 'privilege_id' => 62, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => 62));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 62));
    }
}