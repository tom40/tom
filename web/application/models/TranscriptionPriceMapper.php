<?php

class Application_Model_TranscriptionPriceMapper extends App_Db_Table
{
	protected $_name = 'transcription_prices';

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

    /**
     * Get prices for a transcription type
     *
     * @param int $transctiptionTypeId Transcription type id
     *
     * @return array
     */
    public function getTranscriptionTypePrices($transcriptionTypeId)
    {
        $select = $this->getAdapter()
            ->select()
            ->from(array('tp' => $this->_name), array('*'))
            ->where('transcription_type_id = ?', $transcriptionTypeId);

        return $this->getAdapter()->fetchAll($select);
    }

    public function getTranscriptionTypes($clientId = null)
    {
        $select = $this->getAdapter()->select()
            ->from(array('tp' => $this->_name), array('*'))
            ->joinInner(array('tt' => 'lkp_transcription_types'), 'tt.id = tp.transcription_type_id', array('transcriptionName' => 'name'))
            ->joinInner(array('ta' => 'lkp_turnaround_times'), 'ta.id = tp.turnaround_time_id', array('turnaroundName' => 'name'));

            if (!empty($clientId))
            {
                $select->where('tt.client_id = ?', $clientId);
            }
            else
            {
                $select->where('tt.client_id IS NULL');
            }

        $select->order('tt.id');
        $select->order('ta.id');

        $transcriptionTypes = $this->getAdapter()->fetchAll($select);
        return $transcriptionTypes;
    }

    public function fetchTurnaroundTimesForDropdown($clientId = null)
	{
        $db      = $this->getAdapter();
        $results = array();
        if (!empty($clientId))
        {
            $select = $db->select();
            $select->from(array('tp' => $this->_name), array());
            $select->joinInner(array('tt'  => 'lkp_transcription_types'), 'tt.id = tp.transcription_type_id', array());
            $select->joinInner(array('ltt' => 'lkp_turnaround_times'), 'ltt.id = tp.turnaround_time_id', array('key' =>'id', 'value' => 'name'));
            $select->where('tt.client_id = ?', $clientId);
            $results = $db->fetchAll($select);
        }

        if (0 === count($results))
        {
            $select = $db->select();
            $select->from(array('ltt' => 'lkp_turnaround_times'), array('key' =>'id', 'value' => 'name'));
            $select->joinInner(array('tt' => 'lkp_transcription_types'), 'tt.turnaround_id = ltt.id');
            $select->where('tt.client_id IS NULL');
            $select->order('sort_order');
            $results = $db->fetchAll($select);
        }

		return $results;
	}

    public function fetchTurnaroundTimesForQuoteGenerator($transcriptionTypeId = null)
	{
        $results = array();
        if (null !== $transcriptionTypeId)
        {
            $db      = $this->getAdapter();
            $select = $db->select();
            $select->from(array('ltt' => 'lkp_turnaround_times'), array('key' =>'id', 'value' => 'name'));
            $select->joinInner(array('tp' => 'transcription_prices'), 'tp.turnaround_time_id = ltt.id');
            $select->where('tp.transcription_type_id = ' . $transcriptionTypeId);
            $select->order('sort_order');
            $results = $db->fetchAll($select);
        }
		return $results;
	}

    public function updatePrice($priceId, $price)
    {
        $data = array('price' => $price);
        $this->update($data, 'id = ' . $priceId);
    }

    /**
     * Check an array on of turnaround time ids against a single transcription type id.
     * If that combination is not in the table, insert it.  Delete rows that are not in
     * the array
     *
     * @param int   $transcriptionTypeId Transcription ID
     * @param array $turnarounds         Turn arround times
     *
     * @return void
     */
    public function setPriceRows($transcriptionTypeId, $turnarounds)
    {
        $select = $this->getAdapter()
            ->select()
            ->from(array('tp' => $this->_name), array('*'))
            ->where('transcription_type_id = ?', $transcriptionTypeId);

        $currentPrices = $this->getAdapter()->fetchAll($select);
        $compare       = array();
        if ($currentPrices)
        {
            foreach ($currentPrices as $price)
            {
                $compare[$price['id']] = $price['turnaround_time_id'];
            }
        }

        foreach ($turnarounds as $key => $turnaround)
        {
            $arrayKey = array_search($turnaround, $compare);
            if (false !== $arrayKey)
            {
                unset($turnarounds[$key], $compare[$arrayKey]);
            }
            else
            {
                $data = array(
                    'transcription_type_id' => $transcriptionTypeId,
                    'turnaround_time_id'    => $turnaround,
                    'price'                 => 0.0000
                );
                $this->insert($data);
                unset($turnarounds[$key]);
            }
        }
        if (count($compare) > 0)
        {
            foreach ($compare as $compareKey => $turnaround)
            {
                $arrayKey = array_search($turnaround, $turnarounds);
                if (false === $arrayKey)
                {
                    $this->delete("id = '" . $compareKey . "'");
                    unset($compare[$compareKey]);
                }
            }
        }
    }
}