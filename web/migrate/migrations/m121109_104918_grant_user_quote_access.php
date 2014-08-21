<?php

class m121109_104918_grant_user_quote_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 54,
            'controller' => 'quote-generator',
            'action'     => 'index',
            'object'     => 'Application_Model_TranscriptionPriceMapper',
        ));

        $this->insert('acl_privileges', array(
            'id'         => 55,
            'controller' => 'quote-generator',
            'action'     => 'generate',
            'object'     => 'Application_Model_TranscriptionPriceMapper',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 54, 'mode' => 'allow'));
        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 55, 'mode' => 'allow'));
        $this->insert('acl_group_privileges', array( 'group_id' => 3, 'privilege_id' => 54, 'mode' => 'allow'));
        $this->insert('acl_group_privileges', array( 'group_id' => 3, 'privilege_id' => 55, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 54));
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 55));

        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => 54));
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => 55));

        $this->delete('acl_privileges', 'id = :id', array(':id' => 54));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 55));
    }
}