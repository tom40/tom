<?php

class m121102_164528_clear_old_typists_shifts_data extends CDbMigration
{
	public function safeUp()
	{
        $whereStatement = "shift_id NOT IN (1,2,3,4,5,6,7,8,9,10,11,12)";
        $this->delete('typists_shifts', $whereStatement);
	}

	public function safeDown()
	{

	}
}