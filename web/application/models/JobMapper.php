<?php

class Application_Model_JobMapper extends App_Db_Table
{

    const STATUS_COMPLETED = 4;

	protected $_name = 'jobs';
	protected $_useAcl = true;

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_Job_Row';

	public function save( $data, $updateAcl = true )
	{
		if (isset($data['status_id'])) {
			if ($data['status_id'] == 4) {
				// job complete
				$data['completed'] = 1;
			} else {
				$data['completed'] = 0;
			}

		}
		return parent::save( $data, $updateAcl );
	}

	private function makeFetchSelect( $db, $archived = 0 )
	{
        $archivedSql = "";
        if ( null !== $archived )
        {
            $archivedSql =  " and archived = " . $archived;
        }

		$select = $db->select();
		$select->from(array('j' 		=> 'jobs'), array(	'*',
			'job_due_date' 				=> 'job_due_date',
			'job_due_date_unix' 		=> 'UNIX_TIMESTAMP(job_due_date)',
			'due_days' 					=> 'DATEDIFF(job_due_date, CURDATE())',
			'due_hours'					=> 'HOUR(TIMEDIFF(job_due_date, NOW()))',
            'audio_job_count'           => "(SELECT count(*) from audio_jobs where job_id = j.id" . $archivedSql . " AND deleted IS NULL)",
			//'audio_job_count' 			=> 'fn_AudioJobCountByJobId(j.id)',
			'support_file_count' 		=> 'fn_SupportFileCountByJobId(j.id)')
		);

		$select->join(array('c' => 'clients'), 'c.id = j.client_id', array('client' => 'name', 'client_overall_comments' => 'c.comments'));
		$select->join(array('ljs' => 'lkp_job_statuses'), 'ljs.id = j.status_id', array('status' => 'name', 'status_complete' => 'ljs.complete'));

		$select->joinLeft(array('lpf' => 'lkp_priorities'), 'lpf.id = j.priority_id',
			array(
				'priority_name' 		=> 'name',
				'priority_flag_colour' 	=> 'flag_colour',
				'priority_sort_order' 	=> 'sort_order'
			)
		);
		$select->join(array('u' => 'users'), 'u.id = j.created_user_id', array('created_by' => 'name', 'created_by_email' => 'email'));
		$select->join(array('pu' => 'users'), 'pu.id = j.primary_user_id', array('primary_user' => 'pu.name', 'primary_user_email' => 'pu.email', 'user_overall_comments' => 'pu.comments'));

		// add acl checks
		// if not an admin then restrict access only to authorised users
		if (!self::$_acl->isAdmin()) {
			$select->join(
				array('acl' => 'acl'),
				'j.acl_resource_id = acl.resource_id ' .
					'AND acl.privilege_id = 9 ' . // list access for jobs
					'AND acl.role_id = ' . $this->_getCurrentUserAclRoleId(), // current users acl_role_id
				array()
			);
		}
        $select->where( 'deleted IS NULL' );
        $select->group('j.id');
		return $select;
	}

    /**
     * Fetch jobs nearing completion
     *
     * @return array
     */
    public function fetchJobsNearingCompletion()
    {
        $db = $this->getAdapter();
        $select = $db->select();
        $select->from(array('j' => 'jobs'), array( 'id', 'j.job_due_date' ));
        $select->join( array( 'u' => 'users' ), 'j.primary_user_id = u.id', array( 'u.name', 'u.email' ) );
        $select->join( array( 's' => 'lkp_job_statuses' ), 'j.status_id = s.id', array( 'status' => 's.name', 's.complete' ) );
        $select->where('j.job_due_date <= DATE_SUB(NOW(), INTERVAL 1 DAY)');
        $select->where('j.archived = 0');
        $select->where('j.deleted IS NULL');
        $results = $db->fetchAll($select);

        return $results;
    }

    /**
     * Get jobs by transcription type
     *
     * @return array
     */
    public function fetchByTranscriptionType($transcriptionTypeId)
    {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('j' => 'jobs'), array('id'));
		$select->where('transcription_type_id = ?', $transcriptionTypeId);
        $select->where('j.archived = 0');
        $select->where('j.deleted IS NULL');
		$results = $db->fetchAll($select);

		return $results;
    }


	public function fetchCurrent($sortField='j.id')
	{
		$db     = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ljs.complete = \'0\'');
		$select->where('j.archived = 0');
        $select->where('j.deleted IS NULL');
		$select->order($sortField);
        
		$results = $db->fetchAll($select);
		return $results;
	}

	public function fetchCompleted( $sortField='j.id', $filterInvoiced = false, $archived = null )
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
		$select->where('ljs.complete = \'1\'');
        $select->where('j.deleted IS NULL');

        if ( null !== $archived )
        {
            $select->where('j.archived = ' . $archived);
        }

        if ( true === $filterInvoiced )
        {
            $select->where( "ljs.invoiced = '0'" );
        }

		$select->order($sortField);

		$results = $db->fetchAll($select);
		return $results;
	}

    /**
     * Fetch invoiced Jobs
     *
     * @param string $sortField
     * @param string $archived
     *
     * @return array
     */
    public function fetchInvoiced( $sortField='j.id', $archived = '0' )
    {
        $db = $this->getAdapter();
        $select = $this->makeFetchSelect( $db );
        $select->where( "ljs.invoiced = '1'" );
        $select->where( "j.archived = ?", $archived );
        $select->where('j.deleted IS NULL');
        $select->order( $sortField );

        $results = $db->fetchAll( $select );
        return $results;
    }

    /**
     * Fetch archived Jobs
     *
     * @param string $sortField
     *
     * @return array
     */
    public function fetchArchived( $sortField='j.id DESC' )
    {
        $db     = $this->getAdapter();
        $select = $this->makeFetchSelect( $db, null );
        $select->where( "j.archived = ?", "1" );
        $select->where('j.deleted IS NULL');
        $select->order( $sortField );

        $results = $db->fetchAll( $select );
        return $results;
    }

    /**
     * Fetch invoiced Jobs
     *
     * @param string $sortField
     * @param string $archived
     *
     * @return array
     */
    public function fetchPendingInvoice( $sortField='j.id', $archived = '0' )
    {
        $db = $this->getAdapter();
        $select = $this->makeFetchSelect( $db );
        $select->where( "ljs.name = 'Pending Invoice'" );
        $select->where( "j.archived = ?", $archived );
        $select->order( $sortField );

        $results = $db->fetchAll( $select );
        return $results;
    }

    /**
     * Archive jobs
     *
     * Archives jobs that were invoiced over 2 months ago
     *
     * @return bool
     */
    public function archiveOldJobs()
    {
        $jobs    = $this->fetchJobsToArchive();

        if ( count( $jobs ) > 0 )
        {
            $audioJobMapper = new Application_Model_AudioJobMapper();

            foreach ( $jobs as  $key => $job )
            {
                $jobData = array( 'archived' => '1', 'archived_date' => date( 'Y-m-d H:i:s' ) );
                $db = $this->getAdapter();
                $db->update( 'jobs', $jobData, 'id = ' . $job['id'] );

                $audioJobs = $audioJobMapper->fetchAll( 'job_id = ' . $job['id'] );

                foreach ( $audioJobs as $audioJob )
                {
                    $audioJob->archived = 1;
                    $audioJob->save();
                    $audioJobArray = $audioJob->toArray();

                    $file = APPLICATION_PATH . '/../data/' . $audioJob['id'];

                    if ( file_exists( $file ) )
                    {
                        $deletedFile = unlink( $file );

                        if ( $deletedFile )
                        {
                            $audioJobArray['file_deleted'] = $deletedFile;
                        }
                    }
                    $jobs[$key]['audio_jobs_archived'][] = $audioJobArray;
                }
            }
            return $jobs;
        }
        return array();
    }

    /**
     * Fetch jobs to archive Jobs
     *
     * @return array
     */
    public function fetchJobsToArchive()
    {
        $db = $this->getAdapter();
        $select = $db->select();
        $select->from(
            array( 'j' => 'jobs' ),
            array(
                'j.id',
                'j.title',
                'j.po_number',
                'j.job_due_date'
            )
        )
            ->joinInner( array( 'js' => 'lkp_job_statuses' ), 'j.status_id = js.id', array() )
            ->where( 'js.invoiced = ?', '1' )
            ->where( 'j.archived = ?', '0' )
            ->where( 'j.invoiced_date <= DATE_SUB(NOW(), INTERVAL 1 MONTH)' )
            ->where('j.deleted IS NULL');

        $results = $db->fetchAll( $select );
        return $results;
    }

    /**
     * Purge old jobs
     *
     * Find already archived jobs and delete file if it exists
     *
     * @return array
     */
    public function purgeOldJobs()
    {
        $results = array();
        $audioJobMapper = new Application_Model_AudioJobMapper();

        $db = $this->getAdapter();
        $select = $db->select();
        $select->from(
               array( 'j' => 'jobs' ),
                   array(
                        'j.id',
                        'j.title',
                        'j.po_number',
                        'j.job_due_date'
                   )
        )
               ->joinInner( array( 'js' => 'lkp_job_statuses' ), 'j.status_id = js.id', array() )
               ->where( 'js.invoiced = ?', '1' )
               ->where( 'j.archived = ?', '1' )
               ->where( 'j.invoiced_date <= DATE_SUB(NOW(), INTERVAL 1 MONTH)' );

        $jobs = $db->fetchAll( $select );

        foreach ( $jobs as $job )
        {
            $audioJobs = $audioJobMapper->fetchAll( 'job_id = ' . $job['id'] );
            foreach ( $audioJobs as $audioJob )
            {
                $file = APPLICATION_PATH . '/../data/' . $audioJob['id'];
                if ( file_exists( $file ) )
                {
                    echo $file  . "\n";
                    $deletedFile = unlink( $file );
                    if ( $deletedFile )
                    {
                        $results[] = $audioJob['id'];
                    }
                }
            }
        }
        return $results;
    }

    public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('j.id = ?', $id);
        $select->group('j.id');

		$results = $db->fetchRow($select);
		return $results;
	}
    
    public function fetchDataForAccessShare($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
        $select->joinLeft(array('tt' => 'lkp_transcription_types'), 'tt.id = j.transcription_type_id', array('transcription_name' => 'name'));

		$select->where('j.id = ?', $id);

		$results = $db->fetchRow($select);
		return $results;
	}

	public function lookupStatusById($id)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('ljs' => 'lkp_job_statuses'));
		$select->where('id = ?', $id);

		$results = $db->fetchRow($select);
		return $results;
	}

    public function getInvoicedStatusId()
    {
        $db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('ljs' => 'lkp_job_statuses'));
		$select->where('invoiced = ?', '1');

		$results = $db->fetchRow($select);
		return $results['id'];
    }

	public function fetchAllStatusesForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('lj' => 'lkp_job_statuses'), array('key' =>'id', 'value' => 'name'));
		$select->order('sort_order');

		$results = $db->fetchAll($select);
		return $results;
	}


    public function fetchStatus($statusId)
    {
        $db = $this->getAdapter();
        $select = $db->select();

        $select->from(array('lj' => 'lkp_job_statuses'), array('*'))
            ->where('lj.id = ?', $statusId);

        $results = $db->fetchRow($select);
        return $results;
    }

	public function hasEmailEachTranscriptOnComplete($id)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('j' => 'jobs'), array('email_each_transcript_on_complete'));
		$select->where('j.id = ?', $id);
		return $db->fetchOne($select);
	}

    /**
     * Get project cost
     *
     * @param int $jobId job ID
     *
     * @return float
     */
    public function getJobPrice($jobId)
    {
        if (is_array($jobId))
        {
            $jobId = $jobId['id'];
        }
        $audioJobMapper = new Application_Model_AudioJobMapper;
        $adHoc          = new Application_Model_AdHocMapper;

        $audioJobs = $audioJobMapper->fetchAll( "job_id = '" . $jobId . "' AND deleted IS NULL" );
        $price = 0;
        if ($audioJobs)
        {
            foreach ($audioJobs as $audioJob)
            {
                $price += $audioJob->calculatePrice();
            }
        }

        $price += $adHoc->getTotalPriceByJob($jobId);
        return $price;
    }

    /**
     * Get discount
     *
     * @param int|array $job Job ID
     *
     * @return float
     */
    public function getJobDiscountPercentage($job)
    {
        if (!is_array($job))
        {
            $job = $this->fetchRow( 'id = ' . $job );
        }

        if (null  !== $job['discount'] && (float) $job['discount'] > 0 )
        {
            $discount = $job['discount'];
        }
        else
        {
            $clientMapper = new Application_Model_ClientMapper;
            $client       = $clientMapper->fetchRow( 'id = ' . $job['client_id'] );
            $discount     = $client['discount'];
        }
        if (empty($discount))
        {
            $discount = 0;
        }
        return (float) $discount;
    }

    public function resetArchivedJobTranscriptionTypeId($id)
    {
		$db = $this->getAdapter();
		$db->query('SET FOREIGN_KEY_CHECKS = 0');
        $db->query('UPDATE jobs SET transcription_type_id = 0 WHERE transcription_type_id = ' . $id . ' AND archived = 1');
        $db->query('SET FOREIGN_KEY_CHECKS = 1');
	}

    /**
     * Get job price with discount
     *
     * @param int|array $job Job ID or row
     *
     * @return float
     */
    public function getJobPriceWithDiscount($job)
    {
        if (!is_array($job))
        {
            $job = $this->fetchById($job);
        }
        $discount = $this->getJobDiscountPercentage($job);
        $price    = $this->getJobPrice($job['id']);

        if ($discount > 0)
        {
            $price = $price - ($price * $discount/100);
        }
        return $price;
    }

    public function deleteAudioJobs( $jobId )
    {
        $audioJobModel = new Application_Model_AudioJobMapper();
        $audioJobModel->update( array( 'deleted' =>  date( 'Y-m-d H:i:s' ) ), "job_id = ' " . $jobId . "'" );
    }

    /**
     * Fetch jobs for report
     *
     * @param array $data Search data array
     *
     * @return Zend_Db_Table_Rowset
     */
    public function fetchJobsForReport( $data = array() )
    {
        $select = $this->select();
        if ( !empty( $data['client_id'] ) && $data['client_id'] !== '0' )
        {
            $select->where( 'client_id = ?', $data['client_id'] );
        }

        if ( !empty( $data['status_id'] ) && $data['status_id'] !== '0' )
        {
            $select->where( 'status_id = ?', $data['status_id'] );
        }

        if ( !empty( $data['date']['start'] ) )
        {
            $startDate = $data['date']['start'];
            if ( false !== strpos( $startDate,'/' ) )
            {
                $startDate = str_replace( '/','-', $startDate );
            }

            $startDate = date( 'Y-m-d H:i:s', strtotime( $startDate ) );
            $select->where( 'job_start_date >= ?', $startDate );
        }

        if ( !empty( $data['date']['end'] ) )
        {
            $endDate = $data['date']['end'];
            if ( strpos( $endDate,'/' ) !== false )
            {
                $endDate = str_replace( '/','-', $endDate );
            }

            $endDate = date( 'Y-m-d', strtotime( $endDate ) );
            $endDate .= ' 23:59:59';
            $select->where( 'job_start_date <= ?', $endDate );
        }

        $select->where( 'deleted IS NULL' );
        return $this->fetchAll( $select );
    }
}

