<?php

class m121004_152900_grant_client_job_access extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('acl_privileges', array(
            'id'         => 49,
            'controller' => 'audio-job',
            'action'     => 'grant-user-access',
            'object'     => 'Application_Model_AudioJobMapper',
        ));

        $this->insert('acl_group_privileges', array(
            'group_id'     => 3,
            'privilege_id' => 49,
            'mode'         => 'allow',
        ));
    }

    public function safeDown()
    {
        $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 3, ':privilegeId' => 49));
        $this->delete('acl_privileges', 'id = :id', array(':id' => 49));
    }
}