<?php

class m120926_133719_add_pass_phrase_to_shadow_table extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('users_shadow', 'pass_phrase', 'VARCHAR(256) DEFAULT NULL AFTER password');
	}

	public function safeDown()
	{
        $this->dropColumn('users_shadow', 'pass_phrase');
	}
}