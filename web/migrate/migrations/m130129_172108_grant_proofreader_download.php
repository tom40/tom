<?php

class m130129_172108_grant_proofreader_download extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 59,
            'controller' => 'audio-job',
            'action'     => 'confirm-download-proofreader',
            'object'     => 'Application_Model_AudioJobMapper',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 59, 'mode' => 'allow'));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 59));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 59));
    }
}