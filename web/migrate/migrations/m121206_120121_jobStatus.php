<?php

class m121206_120121_jobStatus extends CDbMigration
{

	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->addColumn('lkp_job_statuses', 'complete', 'ENUM("0", "1") DEFAULT "0"');
        $this->addColumn('lkp_job_statuses', 'invoiced', 'ENUM("0", "1") DEFAULT "0"');
        $this->update('lkp_job_statuses', array('complete' => '1'), "name = 'Job complete'");
        $this->insert('lkp_job_statuses', array('name' => 'Invoiced', 'sort_order' => '5', 'complete' => '1', 'invoiced' => '1'));
	}

	public function safeDown()
	{
        $this->execute('SET foreign_key_checks = 0');
        $this->dropColumn('lkp_job_statuses', 'complete');
        $this->dropColumn('lkp_job_statuses', 'invoiced');
        $this->delete('lkp_job_statuses', 'name = "Invoiced"');
        $this->execute('SET foreign_key_checks = 1');
	}
}