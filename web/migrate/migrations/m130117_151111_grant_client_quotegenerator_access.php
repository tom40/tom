<?php

class m130117_151111_grant_client_quotegenerator_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 58,
            'controller' => 'quote-generator',
            'action'     => 'trans-turnaround-ajax',
            'object'     => 'Application_Model_TranscriptionPriceMapper',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 3, 'privilege_id' => 58, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => 58));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 58));
    }
}