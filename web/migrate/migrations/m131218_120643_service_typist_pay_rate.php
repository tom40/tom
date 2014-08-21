<?php

class m131218_120643_service_typist_pay_rate extends CDbMigration
{

	public function safeUp()
	{
        $this->addColumn( 's_service', 'has_legal', 'ENUM(\'0\',\'1\') DEFAULT \'0\'' );

        $this->addColumn( 's_service', 'typist_grade_1', 'INT(5) NOT NULL DEFAULT 0' );
        $this->addColumn( 's_service', 'typist_grade_2', 'INT(5) NOT NULL DEFAULT 0' );
        $this->addColumn( 's_service', 'typist_grade_3', 'INT(5) NOT NULL DEFAULT 0' );
        $this->addColumn( 's_service', 'typist_grade_4', 'INT(5) NOT NULL DEFAULT 0' );
        $this->addColumn( 's_service', 'typist_grade_5', 'INT(5) NOT NULL DEFAULT 0' );

        $this->addColumn( 's_service', 'legal_grade_1', 'INT(5) NULL DEFAULT NULL' );
        $this->addColumn( 's_service', 'legal_grade_2', 'INT(5) NULL DEFAULT NULL' );
        $this->addColumn( 's_service', 'legal_grade_3', 'INT(5) NULL DEFAULT NULL' );
        $this->addColumn( 's_service', 'legal_grade_4', 'INT(5) NULL DEFAULT NULL' );
        $this->addColumn( 's_service', 'legal_grade_5', 'INT(5) NULL DEFAULT NULL' );
	}

	public function safeDown()
	{
        $this->dropColumn( 's_service', 'has_legal' );

        $this->dropColumn( 's_service', 'typist_grade_1' );
        $this->dropColumn( 's_service', 'typist_grade_2' );
        $this->dropColumn( 's_service', 'typist_grade_3' );
        $this->dropColumn( 's_service', 'typist_grade_4' );
        $this->dropColumn( 's_service', 'typist_grade_5' );

        $this->dropColumn( 's_service', 'legal_grade_1' );
        $this->dropColumn( 's_service', 'legal_grade_2' );
        $this->dropColumn( 's_service', 'legal_grade_3' );
        $this->dropColumn( 's_service', 'legal_grade_4' );
        $this->dropColumn( 's_service', 'legal_grade_5' );
	}
}