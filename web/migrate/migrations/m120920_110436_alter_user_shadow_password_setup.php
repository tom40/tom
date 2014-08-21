<?php

class m120920_110436_alter_user_shadow_password_setup extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('users_shadow', 'salt', 'CHAR(32) DEFAULT NULL AFTER username');
        $this->alterColumn('users_shadow', 'password', 'VARCHAR(32) DEFAULT NULL');
        $this->addColumn('users_shadow', 'password_reset_key', 'VARCHAR(32) DEFAULT NULL AFTER password');
	}

	public function safeDown()
	{
        $this->dropColumn('users_shadow', 'salt');
        $this->alterColumn('users_shadow', 'password', 'VARCHAR(16) NOT NULL');
        $this->dropColumn('users_shadow', 'password_reset_key');
	}
}