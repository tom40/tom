<?php

class m131002_124805_fix_created_date extends CDbMigration
{
    public function safeUp()
    {
        $query = "update audio_jobs_typists set created_date = due_date where created_date is NULL";
        $this->execute( $query );
    }

    public function safeDown()
    {
        return true;
    }
}