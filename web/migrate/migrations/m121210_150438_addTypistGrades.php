<?php

class m121210_150438_addTypistGrades extends CDbMigration
{
	public function safeUp()
	{
        $this->insert('lkp_typist_grades', array('name' => 'Priority Legal', 'sort_order' => '6'));
        $this->insert('lkp_typist_grades', array('name' => 'Legal Only', 'sort_order' => '7'));
	}

	public function safeDown()
	{
        $this->delete('lkp_typist_grades', "name = 'Priority Legal'");
        $this->delete('lkp_typist_grades', "name = 'Legal Only'");
	}
}