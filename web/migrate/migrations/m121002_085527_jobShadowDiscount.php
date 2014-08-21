<?php

class m121002_085527_jobShadowDiscount extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('jobs_shadow', 'discount', 'DECIMAL(6,4) DEFAULT NULL');
	}

	public function safeDown()
	{
        $this->dropColumn('jobs_shadow', 'discount');
	}
}