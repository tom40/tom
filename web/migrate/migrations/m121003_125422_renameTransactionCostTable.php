<?php

class m121003_125422_renameTransactionCostTable extends CDbMigration
{
	public function safeUp()
	{
        $this->renameTable('lkp_transcription_turnaround', 'transcription_prices');
	}

	public function safeDown()
	{
        $this->renameTable('transcription_prices', 'lkp_transcription_turnaround');
	}
}