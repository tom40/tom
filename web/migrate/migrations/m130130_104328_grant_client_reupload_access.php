<?php

class m130130_104328_grant_client_reupload_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 61,
            'controller' => 'job',
            'action'     => 'reupload',
            'object'     => 'Application_Model_JobMapper',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 3, 'privilege_id' => 61, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => 61));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 61));
    }
}