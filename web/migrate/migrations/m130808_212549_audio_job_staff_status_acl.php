<?php

class m130808_212549_audio_job_staff_status_acl extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 63,
            'controller' => 'audio-job',
            'action'     => 'show-edit-statuses',
            'object'     => 'Application_Model_AudioJobMapper',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 63, 'mode' => 'allow'));

        $this->insert('acl_privileges', array(
            'id'         => 64,
            'controller' => 'audio-job',
            'action'     => 'update-status',
            'object'     => 'Application_Model_AudioJobMapper',
        ));

        $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => 64, 'mode' => 'allow'));

    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 63));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 63));

        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => 64));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 64));
    }
}