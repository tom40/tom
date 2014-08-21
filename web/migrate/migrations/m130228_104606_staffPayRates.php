<?php

class m130228_104606_staffPayRates extends CDbMigration
{
	public function safeUp()
	{
        $this->createTable('typist_payrate', array(
                'id'   => 'INT(11) NOT NULL AUTO_INCREMENT',
                'name' => "VARCHAR(255) NOT NULL",
                'PRIMARY KEY (id)'
            ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');

        for ($c = 1; $c < 6; $c++)
        {
            $this->insert(
                'typist_payrate',
                array(
                    'id'   => (string) $c,
                    'name' => (string) $c
                )
            );
        }
        $this->addColumn('typists', 'payrate_id', "INT(11) DEFAULT '1' AFTER note_taker");
        $this->addColumn('typists_shadow', 'payrate_id', "INT(11) DEFAULT '1' AFTER note_taker");

        $this->createTable(
            'transcription_typist_payrate',
            array(
                'id'               => 'INT(11) NOT NULL AUTO_INCREMENT',
                'payrate_id'       => "INT(11) NOT NULL",
                'transcription_id' => "INT(11) NOT NULL",
                'pay_per_minute'   => "INT(11) NOT NULL DEFAULT '0'",
                'PRIMARY KEY (id)'
            ),
            'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8'
        );
	}

	public function safeDown()
	{
        $this->dropTable('typist_payrate');
        $this->dropTable('transcription_typist_payrate');
        $this->dropColumn('typists', 'payrate_id');
        $this->dropColumn('typists_shadow', 'payrate_id');
	}
}