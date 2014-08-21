<?php

class m120919_092500__add_dummy_user extends CDbMigration
{
    public function safeUp()
    {
        // (admin oneresult1234)
        $this->insert('users', array(
            'acl_role_id'  => 1,
            'acl_group_id' => 1,
            'email'        => 'admin@admin.com',
            'username'     => 'administrator',
            'salt'         => '29c6a806b20fb9c109b9f81cd17e7ddb',
            'password'     => '343aefd14e83b814b8cc053364230e23',
            'active'       => 1
            )
        );
    }

    public function safeDown()
    {
        $this->delete('users', 'username = :username', array(':username' => 'admin'));
    }
}