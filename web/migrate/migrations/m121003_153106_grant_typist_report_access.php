<?php

class m121003_153106_grant_typist_report_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 48,
            'controller' => 'report',
            'action'     => 'view',
            'object'     => 'Application_Model_Report'
        ));

        $this->insert('acl_group_privileges', array(
            'group_id'     => 2,
            'privilege_id' => 48,
            'mode'         => 'allow'
        ));
    }

    public function safeDown()
    {
        $this->delete('acl_privileges', 'id = :id', array(':id' => 48));
        $this->delete('acl_group_privileges', 'privilege_id = :privilege_id', array(':privilege_id' => 48));
    }
}