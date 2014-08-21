<?php

class m121004_122231_typistUpdate extends CDbMigration
{
	public function up()
	{
        $this->addColumn('typists', 'note_taker', 'TINYINT(1) DEFAULT "0" AFTER trained_legal');
        $this->addColumn('typists_shadow', 'note_taker', 'TINYINT(1) DEFAULT "0" AFTER trained_legal');
        $this->addColumn('typists', 'full', 'TINYINT(1) DEFAULT "0" AFTER trained_legal');
        $this->addColumn('typists_shadow', 'full', 'TINYINT(1) DEFAULT "0" AFTER trained_legal');
	}

	public function down()
	{
        $this->dropColumn('typists', 'full');
        $this->dropColumn('typists_shadow', 'full');
        $this->dropColumn('typists', 'note_taker');
        $this->dropColumn('typists_shadow', 'note_taker');
	}
}