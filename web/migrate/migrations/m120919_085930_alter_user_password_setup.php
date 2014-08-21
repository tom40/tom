<?php

class m120919_085930_alter_user_password_setup extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('users', 'salt', 'CHAR(32) DEFAULT NULL AFTER username');
        $this->alterColumn('users', 'password', 'VARCHAR(32) DEFAULT NULL');
        $this->addColumn('users', 'password_reset_key', 'VARCHAR(32) DEFAULT NULL AFTER password');
	}

	public function safeDown()
	{
        $this->dropColumn('users', 'salt');
        $this->alterColumn('users', 'password', 'VARCHAR(16) NOT NULL');
        $this->dropColumn('users', 'password_reset_key');
	}
}