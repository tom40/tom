<?php

class m130712_131619_column_indexes extends CDbMigration
{
    public function safeUp()
    {
        $this->createIndex( 'user_id', 'audio_jobs_typists', 'user_id' );
        $this->createIndex( 'shift_id', 'audio_jobs_typists', 'shift_id' );
        $this->createIndex( 'current', 'audio_jobs_typists', 'current' );
        $this->createIndex( 'created_date', 'audio_jobs_typists', 'created_date' );

        $this->createIndex( 'trained_summaries', 'typists', 'trained_summaries' );
        $this->createIndex( 'trained_notes', 'typists', 'trained_notes' );
        $this->createIndex( 'trained_legal', 'typists', 'trained_legal' );
        $this->createIndex( 'full', 'typists', 'full' );

        $this->createIndex( 'user_id', 'typists_shifts', 'user_id' );
    }

    public function safeDown()
    {
        $this->dropIndex( 'user_id', 'audio_jobs_typists' );
        $this->dropIndex( 'shift_id', 'audio_jobs_typists' );
        $this->dropIndex( 'current', 'audio_jobs_typists' );
        $this->dropIndex( 'created_date', 'audio_jobs_typists' );

        $this->dropIndex( 'trained_summaries', 'typists' );
        $this->dropIndex( 'trained_notes', 'typists' );
        $this->dropIndex( 'trained_legal', 'typists' );
        $this->dropIndex( 'full', 'typists' );

        $this->dropIndex( 'user_id', 'typists_shifts' );
    }
}