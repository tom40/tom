<?php

class Application_Model_AudioJobServiceMapper extends App_Db_Table
{
	protected $_name = 'additional_services_audio_jobs';

    /**
     * Delete service
     *
     * @param int $jobId Job ID
     * @param int $serviceId Service ID
     *
     * @return bool
     */
    public function removeService($jobId, $serviceId)
    {
        return $this->getAdapter()->delete(
            $this->_name,
            array(
                'audio_job_id = ?' => $jobId,
                'service_id = ?'   => $serviceId
            )
        );
    }
}