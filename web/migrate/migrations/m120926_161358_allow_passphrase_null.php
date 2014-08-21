<?php

class m120926_161358_allow_passphrase_null extends CDbMigration
{
	public function safeUp()
	{
        $this->alterColumn('users', 'pass_phrase', 'VARCHAR(256) DEFAULT NULL AFTER password');
	}

	public function safeDown()
	{
        $this->alterColumn('users', 'pass_phrase', 'VARCHAR(256) NOT NULL AFTER password');
	}
}