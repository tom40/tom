<?php

class m130325_152340_super_admin_user extends CDbMigration
{

	public function safeUp()
	{
        $connection = Yii::app()->db;
        $command    = $connection->createCommand('SELECT * FROM acl_privileges');
        $rows       = $command->queryAll();

        foreach ($rows as $row)
        {
            $this->insert('acl_group_privileges', array('group_id' => 4, 'privilege_id' => $row['id'], 'mode' => 'allow'));
        }
        $this->insert('acl_groups', array('id' => 4, 'name' => 'Super Admin'));
        $this->update('users', array('acl_group_id' => 4), 'username = :username', array(':username' => 'administrator'));

        $connection = Yii::app()->db;
        $command    = $connection->createCommand("SELECT * FROM acl_privileges WHERE controller IN ('quote-generator', 'report')");
        $rows       = $command->queryAll();

        foreach ($rows as $row)
        {
            $this->delete('acl_group_privileges', 'group_id != :group_id AND privilege_id = :privilege_id', array(':group_id' => 4, ':privilege_id' => $row['id']));
        }
	}

	public function safeDown()
	{
        $this->delete('acl_groups', 'id = :id', array(':id' => 4));
        $this->delete('acl_group_privileges', 'group_id = :group_id', array(':group_id' => 4));
        $this->update('users', array('acl_group_id' => 1), 'username = :username', array(':username' => 'administrator'));

        $connection = Yii::app()->db;
        $command    = $connection->createCommand("SELECT * FROM acl_privileges WHERE controller IN ('quote-generator', 'report')");
        $rows       = $command->queryAll();

        foreach ($rows as $row)
        {
            $this->insert('acl_group_privileges', array('group_id' => 1, 'privilege_id' => $row['id']));
        }
	}
}