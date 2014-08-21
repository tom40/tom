<?php

class m130903_143617_complete_audiojob_cleanup extends CDbMigration
{
	public function safeUp()
	{
        $query = "update audio_jobs set completed = 1 where status_id in( '17', '26', '32', '33' ) or last_status_id in( '17', '26', '32', '33' )";
        $this->execute( $query );
	}

	public function safeDown()
	{
        $query = "update audio_jobs set completed = 0 where status_id in( '17', '26', '32', '33' ) or last_status_id in( '17', '26', '32', '33' )";
        $this->execute( $query );
	}
}