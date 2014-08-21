<?php

class Application_Model_AudioJobMapper extends App_Db_Table
{

    const LEAD_SORT_COL = '38';
    const IS_LEAD_COL   = '39';

	protected $_name = 'audio_jobs';

    protected $_rowClass = 'Application_Model_AudioJob_Row';

	protected $_useAcl = true;

    /**
     * Cache audio job row objects
     * @var array
     */
    protected $_audioJobs = array();

    public function save($data, $updateAcl = true)
	{
		if (isset($data['status_id']))
        {
			if ($data['status_id'] == 17)
            {  // returned to client

				// check there is a transcription file
				$transcriptionFileMapper = new Application_Model_TranscriptionFileMapper();
				$transcript = $transcriptionFileMapper->fetchLatestByAudioJobId($data['id']);
				if (!$transcript)
                {
					return array(
						'error' => 'no_transcript_file',
						'msg'	=> 'Cannot change status to Returned to Client as no transcript file can be found'
					);
				}
			}
            $data['completed'] = $this->_isStatusCompleted($data['status_id']);
		}

        if (!empty($data['lead_id']))
        {
            if (isset($data['id']) && !empty($data['id']))
            {
                $where = $this->getAdapter()
                    ->quoteInto('lead_id = ?', $data['id']);

                $this->update(
                    array(
                        'lead_id' => $data['lead_id']
                    ),
                    $where
                );
            }
        }

        $updateDueDate = true;
        if ( isset( $data['client_due_date'] ) )
        {
            $updateDueDate = false;
        }

        $priceModifiers = null;
        if ( array_key_exists( 'price_modifiers', $data ) )
        {
            if ( is_array( $data['price_modifiers'] ) )
            {
                $priceModifiers = $data['price_modifiers'];
            }
            unset( $data['price_modifiers'] );
        }

		$audioJobId  = parent::save($data, $updateAcl);
        $audioJobRow = $this->fetchRow( 'id = ' . $audioJobId );

        if ( is_array( $priceModifiers ) )
        {
            $audioJobRow->updatePriceModifiers( $priceModifiers );
        }
        $audioJobRow->updateRates();

        if ( $updateDueDate )
        {
            $audioJobRow->updateDueDate();
            $audioJobRow->save();
        }
        return $audioJobId;
    }

    /**
     * Is status completed
     *
     * @param int $statusId Status ID
     *
     * @return int
     */
    protected function _isStatusCompleted($statusId)
    {
        $db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('ljs' => 'lkp_audio_job_statuses'));
		$select->where('id = ?', $statusId);
        $result = $db->fetchRow($select);
        return $result['complete'];
    }

	public function makeFetchSelect( $db, $checkAcl = true, $jobId = null, $archived = 0 )
	{
		$select = $db->select();

		// get the maximum id from audio_jobs_typists
		// This is so that if we only have one typist assigned we have access to their audio_jobs_typists_id reference
		$sql = 'CREATE TEMPORARY TABLE tmp_audio_typists (`id` int(11), `audio_job_id` int(11), `due_date` datetime, KEY `ix_id` (`id`), KEY `ix_audio_job_id` (`audio_job_id`), KEY `ix_due_date` (`due_date`)) ENGINE=InnoDB CHARACTER SET=utf8';
		$db->query($sql);

		// First we find the minimum typist due date for each audio job since we need to show that in the list even if a job is split
		// Once we have the min typist date for each audio job we can then get the audio_jobs_typists id field
		$select->from(array('ajt' => 'audio_jobs_typists'), array('id' => 0, 'audio_job_id', 'MIN(`ajt`.`due_date`)'));
		$select->where('ajt.current = ?', 1);
		$select->group('ajt.audio_job_id');

		// restrict by job_id if available to keep the number of rows int eh tmp table to a minimum
		if (!is_null($jobId)) {
			$select->where('aj.job_id = ?', $jobId);
			$select->join(array('aj' => 'audio_jobs'), 'ajt.audio_job_id = aj.id', array());
		}

		$sql = 'INSERT INTO tmp_audio_typists (audio_job_id, due_date) ' . $select->__toString();

		$db->query($sql);

		// Now get the audio_jobs_typists id field
		$sql = 'UPDATE tmp_audio_typists tat JOIN audio_jobs_typists ajt ON tat.due_date = ajt.due_date AND tat.audio_job_id = ajt.audio_job_id ' .
		         'SET tat.id = ajt.id WHERE ajt.current = 1';

		$db->query($sql);

		$select = $db->select();

		// get the maximum id from audio_jobs_proofreaders
		// This is so that if we only have one proofreader assigned we have access to their audio_jobs_proofreaders_id reference
		$sql = 'CREATE TEMPORARY TABLE tmp_audio_proofreaders (`id` int(11), `audio_job_id` int(11), KEY `ix_id` (`id`), KEY `ix_audio_job_id` (`audio_job_id`)) ENGINE=InnoDB CHARACTER SET=utf8';

		$db->query($sql);
		$select->from(array('ajp' => 'audio_jobs_proofreaders'), array('MAX(`ajp`.`id`)', 'audio_job_id'));
		$select->where('ajp.current = ?', 1);
		$select->group('ajp.audio_job_id');

		// restrict by job_id if available to keep the number of rows int eh tmp table to a minimum
		if (!is_null($jobId)) {
			$select->where('aj.job_id = ?', $jobId);
			$select->join(array('aj' => 'audio_jobs'), 'ajp.audio_job_id = aj.id', array());
		}

		$sql = 'INSERT INTO tmp_audio_proofreaders ' . $select->__toString();

		$db->query($sql);

		$select = $db->select();
		$select->from(array('aj' => 'audio_jobs'),
			array(
				'*',
				'client_due_date' 			=> 'aj.client_due_date',
				'client_due_date_unix' 		=> 'UNIX_TIMESTAMP(aj.client_due_date)',
				'created_date'	 			=> 'UNIX_TIMESTAMP(aj.created_date)',
				'due_days' 					=> 'DATEDIFF(client_due_date, CURDATE())',
				'due_hours' 				=> 'HOUR(TIMEDIFF(client_due_date, NOW()))',
				'typist_count' 				=> 'fn_AudioJobTypistCount(aj.id)',
				'proofreader_count' 		=> 'fn_AudioJobProofreaderCount(aj.id)',
				'transcription_file_count'	=> 'fn_TranscriptionFileCountByAudioJobId(aj.id)',
                'lead_sort'                 => 'IF(aj.lead_id < 1 OR aj.lead_id IS NULL, aj.id, aj.lead_id)',
                'is_lead'                   => 'IF(aj.lead_id < 1 OR aj.lead_id IS NULL, 1, 0)',
                'transcription_type'        => 'IFNULL(`ss`.`name`,`ltt`.`name`)',
                'turnaround_time'           => 'IFNULL(`sta`.`name`,`ltat`.`name`)',
                'speaker_numbers'           => 'IFNULL(`ssn`.`name`,`lsn`.`name`)',
                'turnaround_priority'       => 'IFNULL(`sta`.`sort_order`,`ltat`.`sort_order`)',
                'additional_services'       => 'GROUP_CONCAT(pm.name SEPARATOR ", ")'
			)
		);
		$select->join(array('j' => 'jobs'), 'aj.job_id = j.id', array('job_due_date' => 'UNIX_TIMESTAMP(j.job_due_date)'));
		$select->join(array('c' => 'clients'), 'j.client_id = c.id', array('client_name' => 'name', 'client_id' => 'id', 'client_overall_comments' => 'c.comments'));
		$select->join(array('lajs' => 'lkp_audio_job_statuses'), 'lajs.id = aj.status_id', array('status' => 'name', 'clientStatus' => "IF (lajs.id IN (1,2,17,26), lajs.name, 'In progress')" , 'completed' => 'complete'));

        $select->joinLeft(array('ltt' => 'lkp_transcription_types'), 'ltt.id = aj.transcription_type_id AND aj.service_id IS NULL', array());
		$select->joinLeft(array('ltat' => 'lkp_turnaround_times'), 'ltat.id = aj.turnaround_time_id  AND aj.service_id IS NULL', array());
		$select->joinLeft(array('lsn' => 'lkp_speaker_numbers'), 'lsn.id = aj.speaker_numbers_id  AND aj.service_id IS NULL', array());

        $select->joinLeft( array( 'ss' => 's_service' ), 'ss.id = aj.service_id AND aj.service_id IS NOT NULL' , array() );
        $select->joinLeft( array( 'sta' => 's_turnaround_time' ), 'sta.id = aj.turnaround_time_id AND aj.service_id IS NOT NULL' , array() );
        $select->joinLeft( array( 'ssn' => 's_speaker_number' ), 'ssn.id = aj.speaker_numbers_id AND aj.service_id IS NOT NULL' , array() );

		$select->joinLeft(array('tat' => 'tmp_audio_typists'), 'tat.audio_job_id = aj.id', array());
		$select->joinLeft(array('ajt' => 'audio_jobs_typists'), 'ajt.id = tat.id', array('audio_jobs_typists_id' => 'id', 'typist_due_date' => 'due_date','typist_due_date_unix' => 'UNIX_TIMESTAMP(ajt.due_date)', 'typist_due_days' => 'DATEDIFF(ajt.due_date, CURDATE())', 'typist_due_hours' => 'HOUR(TIMEDIFF(ajt.due_date, NOW()))','accepted' => 'accepted', 'downloaded' => 'downloaded'));
		$select->joinLeft(array('u' => 'users'), 'u.id = ajt.user_id', array('typist_name' => 'name'));

        $select->joinLeft( array( 'apm' => 'audio_jobs_price_modifiers' ), 'apm.audio_job_id = aj.id', array() );
        $select->joinLeft( array( 'sspm' => 's_service_price_modifier' ), 'sspm.id = apm.service_price_modifier_id', array() );
        $select->joinLeft( array( 'pm' => 's_price_modifier' ), 'pm.id = sspm.price_modifier_id', array() );


		$select->joinLeft(array('tap' => 'tmp_audio_proofreaders'), 'tap.audio_job_id = aj.id', array());
		$select->joinLeft(array('ajp' => 'audio_jobs_proofreaders'), 'ajp.id = tap.id', array('audio_jobs_proofreaders_id' => 'id', 'proofreader_due_date' => 'due_date','proofreader_due_date_unix' => 'UNIX_TIMESTAMP(ajp.due_date)', 'proofreader_due_days' => 'DATEDIFF(ajp.due_date, CURDATE())', 'proofreader_due_hours' => 'HOUR(TIMEDIFF(ajp.due_date, NOW()))', 'proofreader_accepted' => 'accepted', 'proofreader_downloaded' => 'downloaded'));
		$select->joinLeft(array('up' => 'users'), 'up.id = ajp.user_id', array('proofreader_name' => 'name'));

		$select->joinLeft(array('uc' => 'users'), 'uc.id = aj.created_user_id', array('created_user_name' => 'name'));
		$select->joinLeft(array('lpf' => 'lkp_priorities'), 'lpf.id = aj.priority_id',
			array(
					'priority_name' 		=> 'name',
					'priority_flag_colour' 	=> 'flag_colour',
					'priority_sort_order' 	=> 'sort_order'
			)
		);
		$select->join(array('cu' => 'users'), 'cu.id = aj.created_user_id', array('created_by' => 'name'));
		$select->joinLeft(array('pu' => 'users'), 'pu.id = j.primary_user_id', array('job_primary_user' => 'name', 'user_overall_comments' => 'pu.comments'));

        if ( null !== $archived )
        {
		    $select->where( 'aj.archived = ?', $archived );
        }
        $select->where( 'aj.deleted IS NULL' );

		// add acl checks
		// if not an admin then restrict access only to authorised users
		if ( !self::$_acl->isAdmin() && $checkAcl )
        {
			$select->join(array('acl' => 'acl'), 'aj.acl_resource_id = acl.resource_id ' .
						'AND acl.privilege_id = 18 ' . // list access for audio jobs
						'AND acl.role_id = ' . $this->_getCurrentUserAclRoleId() . ' ' . // current users acl_role_id
						'AND acl.mode = \'allow\''
			, array());
		}

        $select->group('aj.id');

		return $select;
	}

	private function _destroyTmptables($db) {
		$sql = 'DROP TEMPORARY TABLE IF EXISTS tmp_audio_typists';

		$db->query($sql);
		$sql = 'DROP TEMPORARY TABLE IF EXISTS tmp_audio_proofreaders';

		$db->query($sql);
	}

	public function fetchCurrent( $jobId = null, $sortField='aj.id', $where = null, $having = null, $checkAcl = true )
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect( $db, $checkAcl );

		if (!is_null($jobId)) {
			$select->where('aj.job_id = ?', $jobId);
		}

		$select->where('aj.completed = 0');

        if (null === $sortField)
        {
            $select->order(self::LEAD_SORT_COL . ' ASC');
            $select->order(self::IS_LEAD_COL . ' DESC');
        }
        else
        {
            $select->order($sortField);
        }

        if (null !== $where)
        {
            $select->where($where);
        }

        if (null !== $having)
        {
            $select->having($having);
        }

		$results = $db->fetchAll($select);
		$this->_destroyTmptables($db);
		return $results;
	}

    public function fetchNonArchived($jobId = null, $sortField= null)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		if (!is_null($jobId)) {
			$select->where('aj.job_id = ?', $jobId);
		}

        if (null === $sortField)
        {
            $select->order(self::LEAD_SORT_COL . ' ASC');
            $select->order(self::IS_LEAD_COL . ' DESC');
        }
        else
        {
            $select->order($sortField);
        }

        $results = $db->fetchAll($select);
		$this->_destroyTmptables($db);

		return $results;

	}

    /**
     * @param null $jobId
     * @param null $sortField
     * @param null $archived
     * @return array
     */
    public function fetchAllArchiveOption($jobId = null, $sortField = null, $archived = null)
    {
        $db = $this->getAdapter();
        $select = $this->makeFetchSelect($db, true, $jobId, $archived);

        if (!is_null($jobId))
        {
            $select->where('aj.job_id = ?', $jobId);
        }

        if (null === $sortField)
        {
            $select->order(self::LEAD_SORT_COL . ' ASC');
            $select->order(self::IS_LEAD_COL . ' DESC');
        }
        else
        {
            $select->order($sortField);
        }

        //echo $select; die;

        $results = $db->fetchAll($select);
        $this->_destroyTmptables($db);

        return $results;

    }

	public function fetchNonArchivedLead($jobId = null, $sortField='aj.id')
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		if (!is_null($jobId)) {
			$select->where('aj.job_id = ?', $jobId);
		}

        $select->where('aj.lead_id IS NULL OR aj.lead_id = 0');

		$select->order($sortField);

		$results = $db->fetchAll($select);
		$this->_destroyTmptables($db);

		return $results;

	}

	public function countNonArchivedAndNonCompleted($jobId = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(array('aj' => 'audio_jobs'), array('count' => 'COUNT(aj.id)'));

		if (!is_null($jobId))
        {
			$select->where('aj.job_id = ?', $jobId);
		}

        $select->join(array('lkajs' => 'lkp_audio_job_statuses'), 'lkajs.id = aj.status_id');
		$select->where('aj.archived = 0');
        $select->where('aj.deleted IS NULL');
		$select->where('lkajs.complete = 0');

		$result = $db->fetchOne($select);

		return $result;

	}

	public function fetchAclResourceIdById($id)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(array('aj' => 'audio_jobs'), array('acl_resource_id'));
		$select->where('id = ?', $id);
		$results = $db->fetchRow($select);
		return $results;
	}

	public function fetchByStatus($statusId = null, $sortField='aj.id')
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		if (!is_null($statusId)) {
			$select->where('aj.status_id = ?', $statusId);
		}
		$select->where('aj.completed = 0');
        if (null === $sortField)
        {
            $select->order(self::LEAD_SORT_COL . ' ASC');
            $select->order(self::IS_LEAD_COL . ' DESC');
        }
        else
        {
            $select->order($sortField);
        }
		$results = $db->fetchAll($select);
		$this->_destroyTmptables($db);
		return $results;
	}

	public function fetchByDateRange($dateFrom = null, $dateTo = null, $sortField='aj.id')
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		if (!is_null($dateFrom) && !is_null($dateTo)) {
			$select->where('aj.client_due_date >= ?', $dateFrom);
			$select->where('aj.client_due_date <= ?', $dateTo);
		}
		$select->where('aj.completed = 0');
        if (null === $sortField)
        {
            $select->order(self::LEAD_SORT_COL . ' ASC');
            $select->order(self::IS_LEAD_COL . ' DESC');
        }
        else
        {
            $select->order($sortField);
        }

		$results = $db->fetchAll($select);
		$this->_destroyTmptables($db);
		return $results;
	}

	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('aj.id = ?', $id);

		$results = $db->fetchRow($select);
		$this->_destroyTmptables($db);
		return $results;
	}

    /**
     * Basic version of fetch by Id
     *
     * @param int $audioJobId Audio Job ID
     *
     * @return Zend_Db_Row
     */
    public function fetchByIdBasic($audioJobId)
    {
        return $this->fetchRow("id = '$audioJobId'");
    }

    public function fetchChildrenLength($id)
	{
		$db = $this->getAdapter();
		$select = $db->select();
        $select->from(array('a' => $this->_name), array('length' => "(SELECT SUM(length_seconds) FROM {$this->_name} WHERE lead_id = {$id})"));
        $select->where('a.lead_id = ?', $id);
		$result = $db->fetchOne($select);
		return $result;
	}

	public function fetchByIdArray($array)
	{
		if (count($array) == 0) {
			return false;
		}

		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('aj.id in (' . implode(',', $array) . ')');

		$results = $db->fetchAll($select);
		$this->_destroyTmptables($db);

		return $results;
	}

	public function fetchByUploadKey($uploadKey, $sortField='aj.id')
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('aj.upload_key = ?', $uploadKey);
		$select->order($sortField);

		$results = $db->fetchAll($select);

		$this->_destroyTmptables($db);
		return $results;
	}

    /**
     * Fetch statuses for drop down
     *
     * @param bool|string $staffOnly false|typist|proofreader
     *
     * @return array
     */
    public function fetchAllStatusesForDropdown( $staffOnly = false )
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('lajs' => 'lkp_audio_job_statuses'), array('key' =>'id', 'value' => 'name', 'show_warning' => 'show_warning'));

        if ( 'typist' === $staffOnly )
        {
            $select->where( 'typist_editable = ?', '1' );
        }
        elseif ( 'proofreader' === $staffOnly )
        {
            $select->where( 'proofreader_editable = ?', '1' );
        }

        $select->order('sort_order');

		$results = $db->fetchAll($select);
		return $results;
	}

	public function fetchToStatusesForDropdown($fromStatusId, $leadId = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('lajs' => 'lkp_audio_job_statuses'), array('key' =>'id', 'value' => 'name', 'show_warning' => 'show_warning'));
		$select->join(array('lajsr' => 'lkp_audio_job_statuses_rules'), 'lajs.id = lajsr.to_status_id', array());
        if (empty($leadId))
        {
            $select->where('lajs.is_child = 0');
        }

		$select->where('lajsr.from_status_id = ?', $fromStatusId);
		$select->order('sort_order');

		$results = $db->fetchAll($select);
		return $results;
	}

	public function getUploadKeyCountForFilesWithLengthIsNotNull($uploadKey)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('aj' => 'audio_jobs'), array('COUNT(id)'));
		$select->where('upload_key = ?', $uploadKey);
		$select->where('length_seconds is not null');

		$results = $db->fetchOne($select);
		return $results;
	}

	public function lookupStatusById($id)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('lajs' => 'lkp_audio_job_statuses'));
		$select->where('id = ?', $id);

		$results = $db->fetchRow($select);
		return $results;
	}

    /**
     * Fetch jobs for client
     *
     * @param int      $jobId     Job ID
     * @param bool|int $lead      If true lead only, if int lead and children
     * @param string   $sortField Field to sort by
     *
     * @return type
     */
	public function fetchForClientByJobId($jobId, $lead = false, $sortField= null)
	{
        $db = $this->getAdapter();
		$select = $this->makeFetchSelect($db, true, null, null);

		if (!is_null($jobId)) {
			$select->where('aj.job_id = ?', $jobId);
		}

        if (true === $lead)
        {
            $select->where('aj.lead_id IS NULL OR aj.lead_id = 0');
        }
        elseif (is_numeric($lead))
        {
            $select->where('aj.lead_id = ?', $lead);
            $select->orWhere('aj.id = ?', $lead);
        }

        if (null === $sortField)
        {
            $select->order(self::LEAD_SORT_COL . ' ASC');
            $select->order(self::IS_LEAD_COL . ' DESC');
        }
        else
        {
            $select->order($sortField);
        }

        $select->group('aj.id');
        $results = $db->fetchAll($select);
		$this->_destroyTmptables($db);

		return $results;

	}

    /**
     * Calculate due date based on turn around time
     *
     * @param int $taId Turn around time ID
     *
     * @return string
     */
    protected function _calculateAudioJobDueDate($taId)
	{
		$taMapper = new Application_Model_TurnaroundTimeMapper();
        return $taMapper->calculateDueDate($taId);
	}

	public function fetchJobId($id)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('aj' => 'audio_jobs'), array('job_id'));
        $select->joinInner(array('j' => 'jobs'), 'j.id = aj.job_id', array('job_title' => 'title'));
		$select->where('aj.id = ?', $id);

		$results = $db->fetchRow($select);
		return $results;
	}

	public function fetchCountByJobId($jobId)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('aj' => 'audio_jobs'), array('COUNT(id)'));
		$select->where('job_id = ?', $jobId);
		$select->where('archived = 0');
        $select->where('deleted IS NULL');

		$results = $db->fetchOne($select);
		return $results;
	}

	public function fetchCompletedCountByJobId($jobId)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('aj' => 'audio_jobs'), array('COUNT(id)'));
		$select->where('job_id = ?', $jobId);
		$select->where('completed = 1');
        $select->where('deleted IS NULL');
		$select->where('archived = 0');

		$results = $db->fetchOne($select);
		return $results;
	}

    public function fetchJobsForReport($data = array())
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('aj' => 'audio_jobs'),
                array('*',
                    'status_name'          => 'js.name',
                    'audio_job_id'         => 'id',
                    'lead_sort'            => 'IF(aj.lead_id < 1 OR aj.lead_id IS NULL, aj.id, aj.lead_id)',
                    'is_lead'              => 'IF(aj.lead_id < 1 OR aj.lead_id IS NULL, 1, 0)',
                    'transcription_type'   => 'IFNULL(`ss`.`name`,`ltt`.`name`)',
                    'turnaround_time'      => 'IFNULL(`sta`.`name`,`ltat`.`name`)',
                    'speaker_numbers'      => 'IFNULL(`ssn`.`name`,`lsn`.`name`)',
                    'turnaround_priority'  => 'IFNULL(`sta`.`sort_order`,`ltat`.`sort_order`)'
                ))
               ->joinInner(array('j' => 'jobs'), 'j.id = aj.job_id', array('po_number', 'job_start_date', 'job_due_date'))
               ->joinInner(array('u' => 'users'), 'u.id = j.primary_user_id', array('contact_name' => 'name'))
               ->joinInner(array('c' => 'clients'), 'c.id = j.client_id', array('client' => 'name'))

               ->joinLeft(array('ltt' => 'lkp_transcription_types'), 'ltt.id = aj.transcription_type_id AND aj.service_id IS NULL', array())
		       ->joinLeft(array('ltat' => 'lkp_turnaround_times'), 'ltat.id = aj.turnaround_time_id  AND aj.service_id IS NULL', array())
		       ->joinLeft(array('lsn' => 'lkp_speaker_numbers'), 'lsn.id = aj.speaker_numbers_id  AND aj.service_id IS NULL', array())

               ->joinLeft( array( 'ss' => 's_service' ), 'ss.id = aj.service_id AND aj.service_id IS NOT NULL' , array() )
               ->joinLeft( array( 'sta' => 's_turnaround_time' ), 'sta.id = aj.turnaround_time_id AND aj.service_id IS NOT NULL' , array() )
               ->joinLeft( array( 'ssn' => 's_speaker_number' ), 'ssn.id = aj.speaker_numbers_id AND aj.service_id IS NOT NULL' , array() )

               ->join(array('js' => 'lkp_job_statuses'), 'j.status_id = js.id', array());


        if (!empty($data['client_id']) && $data['client_id'] !== '0')
        {
            $select->where('j.client_id = ' . $data['client_id']);
        }

        if (!empty($data['status_id']) && $data['status_id'] !== '0')
        {
            $select->where('j.status_id = ' . $data['status_id']);
        }

        if (!empty($data['date']['start']))
        {
            $startDate = $data['date']['start'];
            if (strpos($startDate,'/') !== false)
            {
               $startDate = str_replace('/','-', $startDate);
            }

            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $select->where("j.job_start_date >= '$startDate'");
        }

        if (!empty($data['date']['end']))
        {
            $endDate = $data['date']['end'];
            if (strpos($endDate,'/') !== false)
            {
               $endDate = str_replace('/','-', $endDate);
            }

            $endDate = date('Y-m-d', strtotime($endDate));
            $endDate .= ' 23:59:59';
            $select->where("j.job_start_date <= '$endDate'");
        }

        $select->where( 'aj.deleted IS NULL' )
               ->order('aj.job_id')
               ->order('32 ASC')
               ->order('33 DESC')
               ->group('aj.id');

		$results = $db->fetchAll($select);

		return $results;
	}

    /**
     * Fetch a list of jobs for staff work report
     *
     * @param array $filterData Filter data array
     *
     * @return array
     */
    public function fetchJobsForStaffWorkReport($filterData)
    {
        $db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('aj' => 'audio_jobs'),
            array(
                'audio_job_id'        => 'aj.id',
                'file_name'           => 'aj.file_name',
                'file_length'         => 'aj.length_seconds',
                'client_name'         => 'c.name',
                'project_title'       => 'j.title',
                't_name'              => 'ut.name',
                't_id'                => 'ajt.user_id',
                't_start'             => 'ajt.minutes_start',
                't_end'               => 'ajt.minutes_end',
                't_assigned_date'     => 'ajt.created_date',
                't_due_date'          => 'ajt.due_date',
                't_comment'           => 'ajt.comment',
                't_substandard'       => 'ajt.substandard_payrate',
                't_replacement'       => 'ajt.replacement_payrate',
                't_paygrade'          => 't.payrate_id',
                't_payrate'           => 'ajt.pay_per_minute',
                'p_name'              => 'up.name',
                'p_id'                => 'ajp.user_id',
                'p_start'             => 'ajp.minutes_start',
                'p_end'               => 'ajp.minutes_end',
                'p_assigned_date'     => 'ajp.created_date',
                'p_due_date'          => 'ajp.due_date',
                'p_comment'           => 'ajp.comment',
                'transcription_type'  => 'IFNULL(`ss`.`name`,`ltt`.`name`)',
                'turnaround_time'     => 'IFNULL(`sta`.`name`,`ltat`.`name`)',
                'speaker_numbers'     => 'IFNULL(`ssn`.`name`,`lsn`.`name`)',
                'additional_services' => 'GROUP_CONCAT(pm.name SEPARATOR ", ")'
            )
        )
            ->joinInner(array('j' => 'jobs'), 'aj.job_id = j.id', array())
            ->joinInner(array('c' => 'clients'), 'j.client_id = c.id', array())
            ->joinLeft(array('ajt' => 'audio_jobs_typists'), "aj.id = ajt.audio_job_id and ( ajt.current = '1' or ajt.substandard_payrate = '1' )", array())
            ->joinLeft(array('ajp' => 'audio_jobs_proofreaders'), "aj.id = ajp.audio_job_id and ajp.current = '1'", array())
            ->joinLeft(array('ut' => 'users'), 'ajt.user_id = ut.id', array())
            ->joinLeft(array('up' => 'users'), 'ajp.user_id = up.id', array())
            ->joinLeft(array('t' => 'typists'), 'ut.id = t.user_id', array())
            ->joinLeft(array('p' => 'proofreaders'), 'up.id = p.user_id', array())

            ->joinLeft(array('ltt' => 'lkp_transcription_types'), 'aj.transcription_type_id = ltt.id', array())
            ->joinLeft(array('ltat' => 'lkp_turnaround_times'), 'ltat.id = aj.turnaround_time_id  AND aj.service_id IS NULL', array())
            ->joinLeft(array('lsn' => 'lkp_speaker_numbers'), 'lsn.id = aj.speaker_numbers_id  AND aj.service_id IS NULL', array())

            ->joinLeft( array( 'apm' => 'audio_jobs_price_modifiers' ), 'apm.audio_job_id = aj.id', array() )
            ->joinLeft( array( 'sspm' => 's_service_price_modifier' ), 'sspm.id = apm.service_price_modifier_id', array() )
            ->joinLeft( array( 'pm' => 's_price_modifier' ), 'pm.id = sspm.price_modifier_id', array() )
            ->joinLeft( array( 'ss' => 's_service' ), 'ss.id = aj.service_id AND aj.service_id IS NOT NULL' , array() )
            ->joinLeft( array( 'sta' => 's_turnaround_time' ), 'sta.id = aj.turnaround_time_id AND aj.service_id IS NOT NULL' , array() )
            ->joinLeft( array( 'ssn' => 's_speaker_number' ), 'ssn.id = aj.speaker_numbers_id AND aj.service_id IS NOT NULL' , array() )

            ->where("(ajp.current = '1' or ( ajt.current = '1' or ajt.substandard_payrate = '1' ) )")
            ->where( "aj.deleted IS NULL" )
            ->group('ajt.id');;

        $dateFilterField = array(
            'typists'      => 'ajt',
            'proofreaders' => 'ajp'
        );

        if (!empty($filterData['date']['start']))
        {
            $startDate = $filterData['date']['start'];
            if (strpos($startDate,'/') !== false)
            {
                $startDate = str_replace('/','-', $startDate);
            }

            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $select->where($dateFilterField[$filterData['group_by']] . ".created_date >= '$startDate'");
        }

        if (!empty($filterData['date']['end']))
        {
            $endDate = $filterData['date']['end'];
            if (strpos($endDate,'/') !== false)
            {
                $endDate = str_replace('/','-', $endDate);
            }

            $endDate = date('Y-m-d', strtotime($endDate));
            $endDate .= ' 23:59:59';
            $select->where($dateFilterField[$filterData['group_by']] . ".created_date <= '$endDate'");
        }

        if (!empty($filterData['status_id']) && $filterData['status_id'] !== '0')
        {
            $select->where('aj.status_id = ' . $filterData['status_id']);
        }

        if (!empty($filterData['client_id']) && $filterData['client_id'] !== '0')
        {
            $select->where('c.id = ' . $filterData['client_id']);
        }

        $userFilter = array();
        if (count($filterData['typists']) > 0)
        {
            $userFilter[] = "t.id IN('" . implode("','", $filterData['typists']) . "')";
        }

        if (count($filterData['proofreaders']) > 0)
        {
            $userFilter[] = "p.id IN('" . implode("','", $filterData['proofreaders']) . "')";
        }

        if (count($userFilter) > 0)
        {
            $select->where('(' . implode(' OR ', $userFilter) . ')');
        }

        if ('typists' === $filterData['group_by'])
        {
            $select->order(array('IF(ISNULL(ut.name),1,0), ut.name ASC', 'ut.id ASC', 'ajt.created_date'));
        }
        else
        {
            $select->order(array('IF(ISNULL(up.name),1,0), up.name ASC', 'up.id ASC', 'ajp.created_date'));
        }
        $results = $db->fetchAll($select);
        return $results;
    }

    /**
     * Fetch all audio jobs by job id
     *
     * @param int  $jobId      Job ID
     * @param bool $leadOnly   If true filter sub files
     * @param int  $audioJobId If true, filter this audio job
     * @param int  $archived   Archived value
     *
     * @return Zend_Db_Table_Rowset
     */
    public function fetchByJobId($jobId, $leadOnly = true, $audioJobId = null, $archived = 0)
    {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('aj' => 'audio_jobs'), array('*'));

		$select->where('job_id = ?', $jobId);

        if ( null !== $archived )
        {
		    $select->where( 'archived = ' . $archived );
        }
        $select->where('deleted IS NULL');

        if ($leadOnly)
        {
            $select->where('aj.lead_id IS NULL OR aj.lead_id = 0');
        }

        if ( is_array( $audioJobId ) )
        {
            $select->where( "id NOT IN('" . implode( "','", $audioJobId ) . "')" );
        }
        elseif (null !== $audioJobId)
        {
            $select->where('id != ?', $audioJobId);
        }

        // add acl checks
		// if not an admin then restrict access only to authorised users
		if (!self::$_acl->isAdmin()) {
			$select->join(array('acl' => 'acl'), 'aj.acl_resource_id = acl.resource_id ' .
						'AND acl.privilege_id = 18 ' . // list access for audio jobs
						'AND acl.role_id = ' . $this->_getCurrentUserAclRoleId() . ' ' . // current users acl_role_id
						'AND acl.mode = \'allow\''
			, array());
		}
        $select->group('aj.id');
		$results = $db->fetchAll($select);
		return $results;
    }

    /**
     * Do the supplied audio jobs have the same parent
     *
     * @param array $audioJobIds Audio job ID arrays
     *
     * @return bool
     */
    public function audioJobsAreSameJob( $audioJobIds )
    {
        $db = $this->getAdapter();
        $select = $db->select();

        $select->from(array('aj' => 'audio_jobs'), array('job_id'))
            ->where( "id IN('" . implode( "','", $audioJobIds ) . "')" )
            ->group( 'aj.job_id' );

        $results = $db->fetchAll( $select );
        return (bool) ( count( $results ) < 2 );
    }

    /**
     * Fetch all audio jobs by lead id
     *
     * @param int  $jobId       Job ID
     * @param bool $includeLead If true include lead
     *
     * @return Zend_Db_Table_Rowset
     */
    public function fetchByLeadId($jobId, $includeLead = false)
    {
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
		$select->where('aj.archived = 0');
        $select->where('deleted IS NULL');
        $select->where('aj.lead_id = ? ', $jobId);

        if ($includeLead)
        {
            $select->orWhere('aj.id = ?', $jobId);
        }

		$results = $db->fetchAll($select);
        $this->_destroyTmptables($db);
		return $results;
    }

    public function fetchByLeadIdBasic($jobId, $includeLead = false)
    {
        $db = $this->getAdapter();
        $select = $db->select();
        $select->from($this->_name);
        $select->where('archived = 0');
        $select->where('deleted IS NULL');
        $select->where('lead_id = ? ', $jobId);

        if ($includeLead)
        {
            $select->orWhere('id = ?', $jobId);
        }

        $results = $db->fetchAll($select);
        return $results;
    }

    /**
     * Is this audio job a lead job
     *
     * @param int $jobId Audio job lead ID
     *
     * @return bool
     */
    public function isLead($jobId)
    {
        $db = $this->getAdapter();
        $select = $db->select();
        $select->from($this->_name, array('count(lead_id)'));
        $select->where('archived = 0');
        $select->where('deleted IS NULL');
        $select->where('lead_id = \'?\' ', $jobId);
        $select->group('lead_id');
        $result = $db->fetchOne($select);
        return ($result > 0);
    }

	public function fetchParentJobPrimaryUserId($id)
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('aj' => 'audio_jobs'), array());
		$select->join(array('j' => 'jobs'), 'aj.job_id = j.id', array('primary_user_id'));
		$select->where('aj.id = ?', $id);

		$results = $db->fetchOne($select);
		return $results;
	}

    public function resetArchivedAudioJobTranscriptionTypeId($id) {
		$db = $this->getAdapter();
		$db->query('SET FOREIGN_KEY_CHECKS = 0');
        $db->query('UPDATE audio_jobs SET transcription_type_id = 0 WHERE transcription_type_id = ' . $id . ' AND archived = 1');
        $db->query('SET FOREIGN_KEY_CHECKS = 1');
	}

	public function changeFileUploadCountByUploadKey($newFileUploadCount, $uploadKey) {
		$db = $this->getAdapter();

		$sql = "update audio_jobs set upload_file_count = " . $newFileUploadCount . " where upload_key = '" . $uploadKey . "'";

		$db->query($sql);

	}

	public function cleanupUploadsByJobId($jobId) {
		$db = $this->getAdapter();

		$sql = "delete from audio_jobs where job_id = " .
			$jobId . " and length_seconds is null and upload_email_notification_sent = 0";

		$db->query($sql);

		$sql = "select max(upload_key) from audio_jobs where job_id = " .
			$jobId . " and length_seconds is not null and upload_email_notification_sent = 0";

		$maxUploadKey = $db->fetchOne($sql);

		if (!is_null($maxUploadKey)) {
			$sql = "select id from audio_jobs where job_id = " .
				$jobId . " and length_seconds is not null and upload_email_notification_sent = 0";

			$results = $db->fetchAll($sql);
			$count = count($results);

			if ($count > 0) {
				foreach ($results as $item) {
					$sql = "update audio_jobs set upload_key = '" . $maxUploadKey . "', upload_file_count = " . $count .
						" where id = " . $item['id'];
					$db->query($sql);
				}
			}
		}

		return $maxUploadKey;

	}

	public function setEmailNotificationByUploadKey($uploadKey) {
		$db = $this->getAdapter();
		$sql = "update audio_jobs set upload_email_notification_sent = 1 where upload_key = '" .
		$uploadKey . "' and length_seconds is not null and upload_email_notification_sent = 0;";
		$db->query($sql);
		return true;
	}

    /**
     * Generates a detailed file name (consisting of file name, transcription type and audio length)
     *
     * @param int $audioJobId
     *
     * @return string
     */
    public function getFullFileName($audioJobId)
    {
        $prettyFileName = '';
        $db             = $this->getAdapter();
		$select         = $db->select();

		$select->from(array('aj' => $this->_name))
            ->join(array('ltt' => 'lkp_transcription_types'), 'ltt.id = aj.transcription_type_id', array('transcription_type' => 'name'))
            ->where('aj.id = ?', $audioJobId);
		$result = $db->fetchRow($select);

        if (!empty($result))
        {
            $length   = $result['length_seconds'];
            if (!empty($length))
            {
                $length  = (($length - ($length % 60))/60)  . ' mins';
            }

            $fileParts     = array($result['file_name'], $result['transcription_type'], $length);
            $fileNameParts = array();
            foreach ($fileParts as $part)
            {
                if (!empty($part))
                {
                    $fileNameParts[] = $part;
                }
            }
            $prettyFileName = implode(' - ' , $fileNameParts);
        }

        return $prettyFileName;
    }

    /**
     * Get audio job cost
     *
     * @param int $audioJobId Audio job ID
     * @param bool  $jobDiscount If true factor in job discount
     *
     * @return float
     */
    public function getAudioJobPrice( $audioJobId, $jobDiscount = false )
    {
        $audioJob = $this->fetchRow( 'id =' . $audioJobId );
        return $audioJob->calculatePrice( $jobDiscount );
    }

    /**
     * Get audio job length. Returns 0 for child audio jobs
     * and adds their time to the lead audio job when it requests for the audio job length
     *
     * @param int  $audioJobId Audio job ID
     * @param bool $addMinute  If true round up spare seconds to an extra minute
     *
     * @return float
     */
    public function getAudioJobLength($audioJobId, $addMinute = false)
    {
        //$audioJob              = $this->fetchById($audioJobId);
        $audioJob = $this->fetchRow( 'id = ' . $audioJobId );
        $audioJobLengthSeconds = 0;

        if (empty($audioJob['lead_id']))
        {
            $audioJobLengthSeconds = (isset($audioJob['length_seconds'])) ? $audioJob['length_seconds'] : 0;

            // Add the length of linked leads to the main lead
            $childrenLengthSeconds = $this->fetchChildrenLength($audioJobId);
            if (!empty($childrenLengthSeconds))
            {
                $audioJobLengthSeconds += $childrenLengthSeconds;
            }
        }

        if ($addMinute)
        {

            $audioJobLengthSeconds = ( $audioJobLengthSeconds + ( ( int ) ( bool ) ( $audioJobLengthSeconds % 60 ) ) * ( 60 - ( $audioJobLengthSeconds % 60 ) ) );
        }
        return $audioJobLengthSeconds;
    }


    /**
     * Populate price information
     *
     * @param array $audioJobs   Audio jobs array
     * @param bool  $jobDiscount If true factor in job discount
     *
     * @return array
     */
    public function populatePrices( $audioJobs, $jobDiscount = false )
    {
        if (is_array($audioJobs) && count($audioJobs) > 0)
        {
            foreach ($audioJobs as $key => $audioJob)
            {
                $audioJobs[$key]['price'] = $this->getAudioJobPrice( $audioJob['id'], $jobDiscount );
            }
        }
        return $audioJobs;
    }

    /**
     * Populate price information
     *
     * @param array $audioJob    Audio jobs array
     * @param bool  $jobDiscount If true factor in job discount
     *
     * @return array
     */
    public function populatePrice( $audioJob, $jobDiscount = false )
    {
        if (!empty($audioJob))
        {
            $audioJob['price'] = $this->getAudioJobPrice( $audioJob['id'], $jobDiscount );
        }
        return $audioJob;
    }

    /**
     * Get audio jobs by transcription type
     *
     * @param int $transcriptionTypeId Transcription type Id
     *
     * @return array
     */
    public function fetchByTranscriptionType($transcriptionTypeId)
    {
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('aj' => 'audio_jobs'), array('job_id'));
		$select->where('transcription_type_id = ?', $transcriptionTypeId);
        $select->where('aj.archived = 0');
        $select->where('aj.deleted IS NULL');
		$results = $db->fetchAll($select);

		return $results;
    }

    /**
     * Fetch audio jobs with client
     *
     * @param array $audioJobIds Array of audio job IDs
     *
     * @return array
     */
    public function fetchWithClient($audioJobIds)
    {
        if (!is_array($audioJobIds))
        {
            $audioJobIds = array($audioJobIds);
        }

        $db = $this->getAdapter();
        $select = $db->select();
        $select = $select->from(
            array('aj' => $this->_name),
            array(
                'aj.*',
                'client_name' => 'c.name',
                'j.po_number',
                'training_code' => 'IFNULL(`ss`.`training_code`,`tt`.`training_code`)',
            )
        )
            ->join(array('j' => 'jobs'), 'aj.job_id = j.id', array())
            ->join(array('c' => 'clients'), 'j.client_id = c.id', array())
            ->joinLeft(array('tt' => 'lkp_transcription_types'), 'tt.id = aj.transcription_type_id AND aj.service_id IS NULL', array())
            ->joinLeft( array( 'ss' => 's_service' ), 'ss.id = aj.service_id AND aj.service_id IS NOT NULL' , array() )
            ->where("aj.id IN('" . implode("','", $audioJobIds) . "')");

        return $db->fetchAll($select);
    }

    /**
     * Convert hours and minutes to seconds
     *
     * @param int $hours
     * @param int $mins
     *
     * @return int
     */
    public function converTimeToSeconds($hours, $mins)
    {
        $seconds = 0;
        if (!empty($hours) || !empty($mins))
        {
            $seconds = ($hours * 3600) + ($mins * 60);
        }

        return $seconds;
    }

    public static function getMinutesFromFileTime( $fileLength )
    {
        $duration = self::getFileDuration( $fileLength, false );
        $minutes  = 0;

        if ($duration['h'] > 0)
        {
            $minutes += ($duration['h'] * 60);
        }

        $minutes += $duration['m'];

        if ($duration['s'] > 0)
        {
            $minutes++;
        }
        return $minutes;
    }

    public static function getFileDuration($lengthSeconds, $display = true)
    {
        $hours   = 0;
        $minutes = 0;
        $seconds = 0;

        if (!empty($lengthSeconds))
        {
            $hours              = floor($lengthSeconds / (60 * 60));
            $divisorForMinutes  = $lengthSeconds % (60 * 60);
            $minutes            = floor($divisorForMinutes / 60);
            $divisionForSeconds = $lengthSeconds % (60);
            $seconds            = floor($divisionForSeconds);
        }

        if (true === $display)
        {
            return $hours . 'h : ' . $minutes . 'm : ' . $seconds . 's';
        }
        else
        {
            return array(
                'h' => $hours,
                'm' => $minutes,
                's' => $seconds
            );
        }
    }

    /**
     * Purge deleted audio files
     *
     * @return array
     */
    public function purgeDeletedFiles()
    {
        $audioJobs = $this->fetchAll( '`deleted` IS NOT NULL' );
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
        return $results;
    }

    /**
     * Purge files with no data base record at all
     *
     * @return array
     */
    public function purgeFilesNoRecords()
    {
        $path = APPLICATION_PATH . '/../data/';
        $it   = new IteratorIterator( new DirectoryIterator( $path ) );

        $model = new Application_Model_AudioJobMapper();

        $results = array();

        for ( $it->rewind(); $it->valid(); $it->next() )
        {
            $file = $it->current();

            if ( !$file->isDir() )
            {
                $audioJobId = $file->getFileName();
                $audioJob   = $model->fetchRow( 'id = ' . $audioJobId );
                if ( !$audioJob )
                {
                    @unlink( $path . $audioJobId );
                    $results[] = $audioJobId;
                }
            }
        }
        return $results;
    }
}

