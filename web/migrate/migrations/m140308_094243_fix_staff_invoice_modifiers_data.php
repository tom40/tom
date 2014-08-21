<?php

class m140308_094243_fix_staff_invoice_modifiers_data extends CDbMigration
{
	public function safeUp()
	{
        $model   = new Application_Model_StaffInvoiceRecordMapper();
        $records = $model->fetchAll( 'service_id > 0' );

        foreach ( $records as $record )
        {
            $record->setPriceModifiers();
            $record->save();
        }
	}

	public function safeDown()
	{
        return true;
	}
}