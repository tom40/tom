<?php

class m130521_154421_proofreader_report_acl extends CDbMigration
{
    public function safeUp()
    {
        $array = array(48, 56, 57);

        foreach ($array as $id)
        {
            $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => $id, 'mode' => 'allow'));
        }
    }

    public function safeDown()
    {
        $array = array(48, 56, 57);

        foreach ($array as $id)
        {
            $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => $id));
        }
    }
}