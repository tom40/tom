<?php

class m130820_125751_staff_invoice_tables extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->createTable('staff_invoice', array(
            'id'            => 'INT(11) NOT NULL AUTO_INCREMENT',
            'name'          => 'VARCHAR(25)',
            'user_id'       => "INT(11) NOT NULL",
            'date_start'    => "DATETIME",
            'date_end'      => 'DATETIME',
            'status_id'     => "INT(11) NOT NULL",
            'accepted_date' => 'DATETIME',
            'paid_date'     => 'DATETIME',
            'created_date'  => 'DATETIME',
            'updated_date'  => 'DATETIME',
            'PRIMARY KEY (id)'
        ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');

        $this->createTable('staff_invoice_records', array(
            'id'                  => 'INT(11) NOT NULL AUTO_INCREMENT',
            'invoice_id'          => "INT(11) NOT NULL",
            'audio_job_typist_id' => "INT(11) NOT NULL",
            'PRIMARY KEY (id)'
        ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');

        $this->createTable('staff_invoice_comments', array(
            'id'           => 'INT(11) NOT NULL AUTO_INCREMENT',
            'invoice_id'   => "INT(11) NOT NULL",
            'comment'      => "TEXT",
            'created_by'   => "INT(11) NOT NULL",
            'created_date' => 'DATETIME',
            'PRIMARY KEY (id)'
        ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');

        $this->createTable('staff_invoice_statuses', array(
            'id'   => 'INT(11) NOT NULL AUTO_INCREMENT',
            'name' => "VARCHAR(25)",
            'PRIMARY KEY (id)'
        ), 'ENGINE = InnoDB DEFAULT CHARACTER SET = utf8');

        $statuses = array(
            'Un-submitted',
            'Pending',
            'Accepted',
            'Rejected',
            'Paid'
        );

        foreach( $statuses as $status )
        {
            $this->insert('staff_invoice_statuses', array(
                'name' => $status
            ));
        }
	}

	public function safeDown()
	{
        $this->dropTable( 'staff_invoice' );
        $this->dropTable( 'staff_invoice_records' );
        $this->dropTable( 'staff_invoice_comments' );
        $this->dropTable( 'staff_invoice_statuses' );
	}
}