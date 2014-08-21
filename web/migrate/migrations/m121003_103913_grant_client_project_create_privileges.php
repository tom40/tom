<?php

class m121003_103913_grant_client_project_create_privileges extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_group_privileges', array(
            'group_id'     => 3,
            'privilege_id' => 9,
            'mode'         => 'allow',
        ));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => 9));
    }
}