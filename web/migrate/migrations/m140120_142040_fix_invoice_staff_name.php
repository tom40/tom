<?php

class m140120_142040_fix_invoice_staff_name extends CDbMigration
{
    public function safeUp()
    {
        $invoiceMapper = new Application_Model_StaffInvoiceMapper();
        $userMapper    = new Application_Model_User();

        $users = $userMapper->fetchAll();

        foreach ( $users as $user )
        {
            $invoiceMapper->update(
                array(
                     'staff_name' => $user->name
                ),
                'user_id = ' . $user->id
            );
        }
    }

    public function safeDown()
    {
        return true;
    }
}