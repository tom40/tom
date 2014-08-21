<?php

class Application_Model_TranscriptionTypistPayrateMapper extends App_Db_Table
{
    protected $_name = 'transcription_typist_payrate';

    /**
     * Update transcription typist pay rates
     *
     * @param int   $transcriptionId Transcription ID
     * @param array $payrateData     Pay rate data id => pay per minute
     *
     * @return bool
     */
    public function updateTranscriptionPayrateData($transcriptionId, $payrateData)
    {
        if (is_array($payrateData) && count($payrateData) > 0)
        {
            $this->deleteTranscriptionPayrates($transcriptionId);

            foreach ($payrateData as $payrateId => $payrate)
            {
                $insertData = array(
                    'transcription_id' => $transcriptionId,
                    'payrate_id'       => $payrateId,
                    'pay_per_minute'   => $payrate
                );
                $this->insert($insertData);
            }
            return true;
        }
        return false;
    }

    /**
     * Fetch pay rates for transaction
     *
     * @param int $transcriptionId Transcription type id
     *
     * @return array
     */
    public function fetchTranscriptionPayrates($transcriptionId)
    {
        $payRates = $this->fetchAll("transcription_id = '" . $transcriptionId . "'");
        $returnData = array();
        foreach ($payRates as $payRate)
        {
            $returnData[$payRate['payrate_id']] = $payRate['pay_per_minute'];
        }
        return $returnData;
    }

    /**
     * Get pay per minute
     *
     * @param int $transcriptionTypeId Transcription Type ID
     * @param int $payrateId           Pay rate ID
     *
     * @return int
     */
    public function getPayPerMinute($transcriptionTypeId, $payrateId)
    {
        $result = $this->fetchRow("transcription_id = '" . $transcriptionTypeId . "' AND payrate_id='" . $payrateId . "'");
        return $result['pay_per_minute'];
    }

    /**
     * Delete transcription pay rates
     *
     * @param int $transcriptionId Transcription type ID
     *
     * @return bool
     */
    public function deleteTranscriptionPayrates($transcriptionId)
    {
        return $this->delete("transcription_id = '" . $transcriptionId . "'");
    }
}