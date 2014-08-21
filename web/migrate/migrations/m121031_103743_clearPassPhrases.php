<?php

class m121031_103743_clearPassPhrases extends CDbMigration
{
	public function safeUp()
	{
        return $this->update('users', array('pass_phrase' => ''), '1');
	}

	public function safeDown()
	{
        return true;
	}
}