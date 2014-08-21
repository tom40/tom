<?php

class m120925_104951_add_pass_phrase extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('users', 'pass_phrase', 'VARCHAR(256) NOT NULL AFTER password');
	}

	public function safeDown()
	{
        $this->dropColumn('users', 'pass_phrase');
	}
}