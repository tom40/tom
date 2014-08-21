<?php

class m130116_172108_grant_proofreader_report_select_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 57,
            'controller' => 'report',
            'action'     => 'select',
            'object'     => 'Application_Model_Report',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 57, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 57));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 57));
    }
}