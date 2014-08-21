<?php

class App_Db_Adapter_Mysqli extends Zend_Db_Adapter_Mysqli
{
    /**
     * Current Transaction Level
     *
     * @var int
     */
    protected $_transactionLevel = 0;

    /**
     * Current Transaction ID
     *
     * @var int
     */
    protected $_transactionId = null;

    /**
     * Begin new DB transaction for connection
     *
     * @param string $notes OPTIONAL
     * @return App_Zend_Db_Adapter_Mysqli
     */
    public function beginTransaction($notes = null)
    {
        if ( $this->_transactionLevel === 0 ) {
            parent::beginTransaction();
            $transactions = new Application_Model_TransactionMapper();
            $transaction = $transactions->createRow();
            $transaction->datetime = date('Y-m-d H:i:s');
            $transaction->user_id  = 1;
            $transaction->notes    = $notes;
            $id = $transaction->save();
            $this->_transactionId = $id;
        }
        $this->_transactionLevel++;

        return $this;
    }

    /**
     * Commit DB transaction
     *
     * @return App_Zend_Db_Adapter_Mysqli
     */
    public function commit()
    {
        if ( $this->_transactionLevel === 1 ) {
            parent::commit();
        }
        $this->_transactionLevel--;

        return $this;
    }

    /**
     * Rollback DB transaction
     *
     * @return App_Zend_Db_Adapter_Mysqli
     */
    public function rollback()
    {
        if ( $this->_transactionLevel === 1 ) {
            parent::rollback();
        }
        $this->_transactionLevel--;

        return $this;
    }

    /**
     * Get adapter transaction level state. Return 0 if all transactions are complete
     *
     * @return int
     */
    public function getTransactionLevel()
    {
        return $this->_transactionLevel;
    }

    /**
     * Get the transaction ID
     *
     * @return int
     */
    public function getTransactionId()
    {
        return $this->_transactionId;
    }

}
