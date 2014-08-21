<?php

class m130821_225529_staff_invoice_permissions extends CDbMigration
{

	public function safeUp()
	{

        $actions = array(
            65 => 'view-invoice',
            66 => 'view-invoices',
            67 => 'generate-invoice',
            68 => 'submit-comment',
            69 => 'transition-invoice',
            70 => 'delete-invoice'
        );

        foreach ( $actions as $id => $action )
        {

            $this->insert('acl_privileges', array(
                'id'         => $id,
                'controller' => 'staff-invoice',
                'action'     => $action,
                'object'     => 'Application_Model_StaffInvoiceMapper',
            ));
            $this->insert('acl_group_privileges', array( 'group_id' => 2, 'privilege_id' => $id, 'mode' => 'allow'));
        }
	}

	public function safeDown()
	{
        $actions = array(
            65,
            66,
            67,
            68,
            69,
            70
        );

        foreach ( $actions as $id )
        {
            $this->delete('acl_group_privileges', 'group_id = :groupId AND privilege_id = :privilegeId', array(':groupId' => 2, ':privilegeId' => $id));
            $this->delete('acl_privileges', 'id = :id', array(':id' => $id));
        }
	}

}