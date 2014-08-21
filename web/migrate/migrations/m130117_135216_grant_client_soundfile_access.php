<?php

class m130117_135216_grant_client_soundfile_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_group_privileges', array( 'group_id' => 3, 'privilege_id' => 29, 'mode' => 'allow'));
        $this->insert('acl_group_privileges', array( 'group_id' => 3, 'privilege_id' => 32, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => 29));
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => 32));
    }
}