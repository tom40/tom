<?php

class m121128_204615_InitialReviewStatus extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('UPDATE `lkp_audio_job_statuses` SET `sort_order` = (`sort_order`+1) WHERE 1');
        $this->insert('lkp_audio_job_statuses', array('name' => 'Initial review', 'sort_order' => '1'));
        $this->execute(
            "INSERT INTO `lkp_audio_job_statuses_rules` VALUES
                ('', (SELECT id FROM `lkp_audio_job_statuses` WHERE `sort_order` = '1'), '1'),
                ('', '1', (SELECT id FROM `lkp_audio_job_statuses` WHERE `sort_order` = '1'))"
        );
	}

    /**
     * Queries must be executed in this order
     *
     * SET foreign_key_checks = 0
     * DELETE FROM lkp_audio_job_statuses_rules
                WHERE `from_status_id` = (SELECT id FROM `lkp_audio_job_statuses` WHERE `sort_order` = '1')
                OR `to_status_id` = (SELECT id FROM `lkp_audio_job_statuses` WHERE `sort_order` = '1')
     * DELETE FROM lkp_audio_job_statuses WHERE sort_order = '1'
     * UPDATE `lkp_audio_job_statuses` SET `sort_order` = (`sort_order`-1) WHERE 1
     * SET foreign_key_checks = 1
     */
	public function safeDown()
	{
        $this->execute('SET foreign_key_checks = 0');
        $this->execute(
            "DELETE FROM lkp_audio_job_statuses_rules
                WHERE `from_status_id` = (SELECT id FROM `lkp_audio_job_statuses` WHERE `sort_order` = '1')
                OR `to_status_id` = (SELECT id FROM `lkp_audio_job_statuses` WHERE `sort_order` = '1')
            "
        );
        $this->delete('lkp_audio_job_statuses', "`sort_order` = '1'");
        $this->execute('UPDATE `lkp_audio_job_statuses` SET `sort_order` = (`sort_order`-1) WHERE 1');
        $this->execute('SET foreign_key_checks = 1');
	}
}