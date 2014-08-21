<?php

class m130814_130457_add_statuses extends CDbMigration
{
    public function safeUp()
    {
        $query = "update lkp_audio_job_statuses set `sort_order` = `sort_order` + 1 where `sort_order` > 21";
        $this->execute( $query );

        $this->insert('lkp_audio_job_statuses', array(
            'id'                   => 31,
            'name'                 => 'Incorrect Duration',
            'proofreader_editable' => 0,
            'typist_editable'      => 0,
            'sort_order'           => 22
        ));

        $this->insert('lkp_audio_job_statuses', array(
            'id'                   => 29,
            'name'                 => 'Inaccurate Portal Information',
            'proofreader_editable' => 1,
            'typist_editable'      => 1,
            'sort_order'           => 27
        ));
        $this->insert('lkp_audio_job_statuses', array(
            'id'                   => 30,
            'name'                 => 'Incomplete Transcript',
            'proofreader_editable' => 1,
            'typist_editable'      => 1,
            'sort_order'           => 28
        ));

        $this->update(
            'lkp_audio_job_statuses',
            array(
                'proofreader_editable' => '1',
            ),
            "id = '16'"
        );

        $this->update(
            'lkp_audio_job_statuses',
            array(
                'proofreader_editable' => '1',
            ),
            "id = '20'"
        );

        $this->update(
            'lkp_audio_job_statuses',
            array(
                'proofreader_editable' => '1',
            ),
            "id = '10'"
        );

        $this->update(
            'lkp_audio_job_statuses',
            array(
                'proofreader_editable' => '1',
            ),
            "id = '18'"
        );
    }

    public function safeDown()
    {
        $this->delete('lkp_audio_job_statuses', 'id = :id', array(':id' => 29));
        $this->delete('lkp_audio_job_statuses', 'id = :id', array(':id' => 30));
        $this->delete('lkp_audio_job_statuses', 'id = :id', array(':id' => 31));

        $this->update(
            'lkp_audio_job_statuses',
            array(
                'proofreader_editable' => '0',
            ),
            "id = '16'"
        );

        $this->update(
            'lkp_audio_job_statuses',
            array(
                'proofreader_editable' => '0',
            ),
            "id = '20'"
        );

        $this->update(
            'lkp_audio_job_statuses',
            array(
                'proofreader_editable' => '0',
            ),
            "id = '10'"
        );

        $this->update(
            'lkp_audio_job_statuses',
            array(
                'proofreader_editable' => '0',
            ),
            "id = '18'"
        );

        $query = "update lkp_audio_job_statuses set `sort_order` = `sort_order` - 1 where `sort_order` > 21";
        $this->execute( $query );
    }
}