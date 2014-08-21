<?php

class m130116_154430_proofreaderReportPerms extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 56,
            'controller' => 'report',
            'action'     => 'update',
            'object'     => 'Application_Model_Report',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 56, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 56));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 56));
    }
}