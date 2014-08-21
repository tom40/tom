<?php

class m121012_104627_grant_typist_shiftbooker_form_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 53,
            'controller' => 'user-shift-booker',
            'action'     => 'ajax-create-shift',
            'object'     => 'Application_Model_TypistsShiftMapper',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 53, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 53));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 53));
    }
}