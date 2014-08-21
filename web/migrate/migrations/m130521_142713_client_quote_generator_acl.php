<?php

class m130521_142713_client_quote_generator_acl extends CDbMigration
{

    public function safeUp()
    {
        $array = array(54, 55, 58);

        foreach ($array as $id)
        {
            $this->insert('acl_group_privileges', array( 'group_id' => 3, 'privilege_id' => $id, 'mode' => 'allow'));
        }
    }

    public function safeDown()
    {
        $array = array(54, 55, 58);

        foreach ($array as $id)
        {
            $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => $id));
        }
    }
}