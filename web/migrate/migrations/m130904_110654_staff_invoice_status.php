<?php

class m130904_110654_staff_invoice_status extends CDbMigration
{

	public function safeUp()
	{
        $this->insert( 'staff_invoice_statuses', array( 'id' => 6, 'name' => 'Returned' ) );

        $this->update(
            'staff_invoice_statuses',
            array(
                'name' => 'Approved for Invoicing',
            ),
            "name = 'Accepted'"
        );

        $this->update(
            'staff_invoice_statuses',
            array(
                'name' => 'Accepted',
            ),
            "name = 'Pending'"
        );
	}

	public function safeDown()
	{
        $this->delete( 'staff_invoice_statuses', 'name = :name', array( ':name' => 'Returned' ) );

        $this->update(
            'staff_invoice_statuses',
            array(
                'name' => 'Pending',
            ),
            "name = 'Accepted'"
        );

        $this->update(
            'staff_invoice_statuses',
            array(
                'name' => 'Accepted',
            ),
            "name = 'Approved for Invoicing'"
        );
	}
}