<?php

class Application_Model_AdHocMapper extends App_Db_Table
{
	protected $_name = 'jobs_extras';

    /**
     * Get additional services for an audio job
     *
     * @param int $audioJobId Audio Job ID
     *
     * @return array
     */
    public function getServicesByJob($jobId)
    {
        $select = $this->getAdapter()->select();
        $select->from($this->_name, array('*'))
            ->where('job_id = ?', $jobId);

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
    public function getTotalPriceByJob($jobId)
    {
        $services = $this->getServicesByJob($jobId);
        $price    = 0;
        if ($services)
        {
            foreach ($services as $service)
            {
                $price += $service['price'];
            }
        }
        return $price;
    }

    public function fetchAllForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('lsn' => $this->_name), array('key' =>'id', 'value' => 'name'));

		$results = $db->fetchAll($select);
		return $results;
	}

    /**
     * Delete
     *
     * @param int $adhocId Adhoc Id
     *
     * @return bool
     */
    public function deleteAdHoc($adhocId)
    {
        return $this->delete('id = ' . $adhocId);
    }
}