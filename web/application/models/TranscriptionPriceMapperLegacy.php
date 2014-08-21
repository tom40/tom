<?php

class Application_Model_TranscriptionPriceMapperLegacy extends App_Db_Table
{
    protected $_name = 'transcription_prices_legacy';

    /**
     * Get price
     *
     * @param int $transcriptionId Transcription ID
     * @param int $turnAroundId    Turn around time ID
     *
     * @return float
     */
    public function getPrice($transcriptionId, $turnAroundId)
    {
        $select = $this->getAdapter()->select()
            ->from($this->_name, array('*'))
            ->where('transcription_type_id = ?', $transcriptionId)
            ->where('turnaround_time_id = ?', $turnAroundId);

        $row = $this->getAdapter()->fetchRow($select);
        if ($row)
        {
            return $row['price'];
        }
        else
        {
            return 0;
        }
    }
}