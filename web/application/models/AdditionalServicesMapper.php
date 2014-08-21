<?php

class Application_Model_AdditionalServicesMapper extends App_Db_Table
{
	protected $_name = 'additional_services';

    /**
     * Get additional services for an audio job
     *
     * @param int $audioJobId Audio Job ID
     *
     * @return array
     */
    public function getServicesByAudioJob($audioJobId)
    {
        $select = $this->getAdapter()->select();
        $select->from(array('asaj' => 'additional_services_audio_jobs'), array('*'))
            ->joinLeft(array('as' => $this->_name), 'asaj.service_id = as.id', array('*'))
            ->where('asaj.audio_job_id = ?', $audioJobId);

        return $this->getAdapter()->fetchAll($select);
    }

    /**
     * Get total cost by audio job
     *
     * @param int $audioJobId Audio Job ID
     * @param int $jobLength  Job length in seconds
     *
     * @return float
     */
    public function getTotalPriceByAudioJob($audioJobId, $jobLength)
    {
        $services = $this->getServicesByAudioJob($audioJobId);
        $price    = 0;
        if ($services)
        {
            foreach ($services as $service)
            {
                $price += ceil($jobLength / (15 * 60)) * $service['price'];
            }
        }
        return $price;
    }

    public function fetchAllForDropdown($filter = false)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('lsn' => $this->_name), array('key' =>'id', 'value' => 'name', 'price' => 'price'));

		$results = $db->fetchAll($select);

        if ($filter && $results)
        {
            $services = $this->getServicesByAudioJob($filter);
            $compare  = array();
            foreach ($services as $service)
            {
                $compare[$service['id']] = true;
            }
            foreach ($results as $key => $result)
            {
                if (isset($compare[$result['key']]))
                {
                    unset($results[$key]);
                }
            }
        }

		return $results;
	}

	public function makeFetchSelect($db, $current=1)
	{
		$select = $db->select();
		$select->from($this->_name);
		return $select;
	}

    /**
     * Fetch specific additional service
     *
     * @param int $id
     *
     * @return array
     */
	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}


    /**
     * Fetch all additional services
     *
     * @return array
     */
    public function fetchAll()
	{
		$db      = $this->getAdapter();
		$select  = $this->makeFetchSelect($db);
		$results = $db->fetchAll($select);
		return $results;
    }

    /**
     * Update Service
     *
     * @param int $serviceId
     * @param array $data
     *
     * @return void
     */
    public function updateService($serviceId, $data)
    {
        $this->update($data, 'id = ' . $serviceId);
    }

    /**
     * Delete Service
     *
     * @param int $serviceId
     *
     * @return void
     */
    public function deleteService($serviceId)
    {
        $this->delete('id = ' . $serviceId);
    }
}