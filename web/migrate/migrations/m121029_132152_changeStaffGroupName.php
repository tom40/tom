<?php

class m121029_132152_changeStaffGroupName extends CDbMigration
{
	public function safeUp()
	{
        $this->update('acl_groups', array('name' => 'Typist/Proofreader'), 'id = 2');
	}

	public function safeDown()
	{
        $this->update('acl_groups', array('name' => 'Staff'), 'id = 2');
	}
}