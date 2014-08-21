<?php

class m121004_210912_transcriptionTypeClientId extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('lkp_transcription_types', 'client_id', 'INT(11) DEFAULT NULL');
        $this->dropColumn('clients', 'custom_pricing');
        $this->dropColumn('clients_shadow', 'custom_pricing');
	}

	public function safeDown()
	{
        $this->dropColumn('lkp_transcription_types', 'client_id');
        $this->addColumn('clients', 'custom_pricing', 'enum("0","1") DEFAULT "0"');
        $this->addColumn('clients_shadow', 'custom_pricing', 'enum("0","1") DEFAULT "0"');
	}
}