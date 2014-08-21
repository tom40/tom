<?php

class Application_Model_AudioJobTypistMapper extends App_Db_Table
{
    /**
     * Typist pay rate is multiplied by this figure if the audio job
     * is marked as poor audio
     */
    const POOR_AUDIO_MODIFER = 1.4;

    /**
     * Model table name
     * @var string
     */
    protected $_name = 'audio_jobs_typists';

    /**
     * Retrieve and save typist pay rate on save
     *
     * @param array $data      Save data array
     * @param bool  $updateAcl Legacy
     *
     * @return mixed
     */
    public function save($data, $updateAcl = true)
    {
        $returnId =  parent::save( $data, $updateAcl );

        if (isset($data['user_id']) && (!isset($data['current']) || '1' == $data['current']))
        {
            $audioJobMapper = new Application_Model_AudioJobMapper();
            $audioJob       = $audioJobMapper->fetchRow( 'id = ' . $data['audio_job_id'] );
            $payRates       = $audioJob->getTypistRates();
            $this->updatePayPerMinute( $data['audio_job_id'], $payRates );
        }
        return $returnId;
    }

    /**
     * Update pay per minute for all current audio job typist records based on a current audio job ID
     *
     * @param int        $audioJobId Audio job ID
     * @param array|null $payRates   Pre calculated pay rates if available
     * @param int        $poorAudio  If audio job is poor audio, modify typist pay rate
     *
     * @return bool
     */
    public function updatePayPerMinute( $audioJobId, $payRates = null, $poorAudio = 0 )
    {
        $typists = $this->fetchByAudioJobId( $audioJobId, false, null, true );
        $results = array();

        $typistModel = new Application_Model_TypistMapper();

        if ( count( $typists ) > 0 )
        {
            foreach ( $typists as $typist )
            {
                if ( count( $payRates ) > 0 )
                {
                    $typistRow = $typistModel->fetchRow( 'user_id = ' . $typist['user_id'] );
                    $payRate   = $payRates[ $typistRow->payrate_id ];
                }
                else
                {
                    $payRate = $this->_getPayRate( $typist['user_id'], $audioJobId );
                }
                if ( $poorAudio > 0 )
                {
                    $payRate = $payRate * $poorAudio;
                }

                $data = array(
                    'pay_per_minute' => $payRate
                );
                $results[$typist['id']] = $this->update( $data, "audio_job_id = '" . $audioJobId . "' AND current = '1'" );
            }
        }
        return $results;
    }

    /**
     * Get pay rate
     *
     * @param int $userId     User ID of typist
     * @param int $audioJobId Audio job ID
     *
     * @return int|float
     */
    protected function _getPayRate( $userId, $audioJobId )
    {
        $audioJobMapper = new Application_Model_AudioJobMapper();
        $typistMapper   = new Application_Model_TypistMapper();
        $payMapper      = new Application_Model_TranscriptionTypistPayrateMapper();
        $typist         = $typistMapper->fetchByUserId( $userId );
        $audioJob       = $audioJobMapper->fetchByIdBasic( $audioJobId );
        return $payMapper->getPayPerMinute($audioJob['transcription_type_id'], $typist['payrate_id']);
    }

    public function makeFetchSelect($db)
	{
		$select = $db->select();
		$select->from(
            array('ajt' => 'audio_jobs_typists'),
            array(
                '*',
                'due_date_unix'   => 'UNIX_TIMESTAMP(ajt.due_date)',
                'due_days'        => 'DATEDIFF(ajt.due_date, CURDATE())',
                'due_hours'       => 'HOUR(TIMEDIFF(ajt.due_date, NOW()))',
                'typist_comment'  => 'comment',
                'speaker_numbers' => 'IFNULL(`ssn`.`name`,`lsn`.`name`)',
            )
        );
		$select->joinLeft(array('u' => 'users'), 'u.id = ajt.user_id', array('typist_name' => 'name', 'acl_role_id' => 'acl_role_id'));

		$select->join(array('aj' => 'audio_jobs'),
			'aj.id = ajt.audio_job_id',
			array(
				'job_id',
				'file_name',
                'internal_comments',
				'link',
				'length_seconds',
				'transcription_file_count'	=> 'fn_TranscriptionFileCountByAudioJobId(aj.id)',
				'support_file_count'		=> 'fn_SupportFileCountByJobId(aj.job_id)',
                'lead_sort'                 => 'IF(aj.lead_id < 1 OR aj.lead_id IS NULL, aj.id, aj.lead_id)',
                'is_lead'                   => 'IF(aj.lead_id < 1 OR aj.lead_id IS NULL, 1, 0)',
                'lead_id',
                'transcription_type'        => 'IFNULL(`ss`.`name`,`ltt`.`name`)',
                'service_id',
                'additional_services'       => 'GROUP_CONCAT(pm.name SEPARATOR ", ")'
			)
		);
		$select->joinLeft(array('ltt' => 'lkp_transcription_types'), 'ltt.id = aj.transcription_type_id AND aj.service_id IS NULL', array());
        $select->joinLeft( array( 'ss' => 's_service' ), 'ss.id = aj.service_id AND aj.service_id IS NOT NULL' , array() );

        $select->joinLeft(array('lsn' => 'lkp_speaker_numbers'), 'lsn.id = aj.speaker_numbers_id  AND aj.service_id IS NULL', array());
        $select->joinLeft( array( 'sta' => 's_turnaround_time' ), 'sta.id = aj.turnaround_time_id AND aj.service_id IS NOT NULL' , array() );
        $select->joinLeft( array( 'ssn' => 's_speaker_number' ), 'ssn.id = aj.speaker_numbers_id AND aj.service_id IS NOT NULL' , array() );

        $select->joinLeft( array( 'apm' => 'audio_jobs_price_modifiers' ), 'apm.audio_job_id = aj.id', array() );
        $select->joinLeft( array( 'sspm' => 's_service_price_modifier' ), 'sspm.id = apm.service_price_modifier_id', array() );
        $select->joinLeft( array( 'pm' => 's_price_modifier' ), 'pm.id = sspm.price_modifier_id', array() );

        $select->join(array('uc' => 'users'), 'uc.id = aj.created_user_id', array('created_user_name' => 'name'));
		$select->join(array('lajs' => 'lkp_audio_job_statuses'), 'lajs.id = aj.status_id', array('status' => 'name'));

		$select->where('aj.archived = 0');
        $select->where('aj.deleted IS NULL');

        $select->group( 'ajt.id' );

		return $select;

	}

    /**
     * Check if the given user has a transcript uploaded for the given file
     *
     * @param int $audioJobId
     * @param int $createdUserId
     *
     * @return array
     */
    public function hasTranscriptionFile($audioJobId, $createdUserId = null)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from(array('tf' => 'transcription_files'));
        $select->joinInner(array('ajt' => $this->_name), 'ajt.user_id = tf.created_user_id AND ajt.audio_job_id = tf.audio_job_id', array());
        $select->where('tf.audio_job_id = ?', $audioJobId);
        if (!empty($createdUserId))
        {
            $select->where('tf.created_user_id = ?', $createdUserId);
        }

        $select->where('tf.archived = 0');
        $select->where('ajt.current = 1');
        $select->order('tf.created_date DESC');
        $select->limit(1);

		$result = $db->fetchAll($select);
        if (!empty($result))
        {
            return true;
        }

        return false;
	}

    /**
     * Check if audio job is split and has typists assigned
     *
     * @param int $audioJobId
     * @param bool $onlyAssigned
     *
     * @return bool
     */
    public function isAssignedSplitAudioJob($audioJobId, $onlyAssigned = false)
    {
        $db = $this->getAdapter();
		$sql = "SELECT count(*) FROM {$this->_name} WHERE audio_job_id = {$audioJobId} AND current = 1";

        if ($onlyAssigned)
        {
            $sql .= " AND user_id <> 0";
        }

        $count = $db->fetchOne($sql);
		if (intval($count) > 0 )
        {
            return true;
        }

        return false;
    }

    /**
     * Returns the number of typists assigned to the audio job
     * ignoring any records kept for the sake of keeping split file timings
     *
     * @param int $audioJobId
     *
     * @return int
     */
    public function getAssignedTypistsCount($audioJobId)
    {
        $db = $this->getAdapter();
		$sql = "SELECT count(*) FROM {$this->_name} WHERE audio_job_id = {$audioJobId} AND current = 1 AND user_id <> 0";
        $count = $db->fetchOne($sql);
		return $count;
    }

    /**
     * Unsplit an audio job
     *
     * @param int $audioJobId
     *
     * @return void
     */
    public function unsplitAudioJob($audioJobId)
    {
        $db = $this->getAdapter();
        $db->update($this->_name, array('current' => '0'), 'audio_job_id = ' . $audioJobId);
    }

	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ajt.id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}

    public function fetchByIdWithTranscriptionType($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
        $select->joinLeft(array('ttype' => 'lkp_transcription_types'), 'ttype.id = aj.transcription_type_id', array('transcription_type' => 'name'));

		$select->where('ajt.id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}

    public function fetchByUserAndAudioJob($userId, $audioJobId)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ajt.audio_job_id = ?', $audioJobId);
		$select->where('ajt.user_id = ?', $userId);
 		$select->where('ajt.current = ?', 1);

		$results = $db->fetchRow($select);
		return $results;
	}



	public function fetchRowByAudioJobIdCurrentUserIdTypistIsCurrent($audioJobId)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ajt.audio_job_id = ?', $audioJobId);
		$select->where('ajt.user_id = ?', Zend_Auth::getInstance()->getIdentity()->id);
 		$select->where('ajt.current = ?', 1);

		$results = $db->fetchRow($select);
		return $results;
	}

    /**
     * Return an array of typist IDs
     *
     * Where the list consists of one current typist and $removeSingle is true, set that user
     * to not current.  This is to avoid a bug where a single user was current, the file is
     * then split and the same user is assigned a chunk, an additional chunk was created.
     *
     * @param int  $audioJobId   Audio job Id
     * @param bool $removeSingle If true and 1 result is returned, delete and return empty array
     *
     * @return array
     */
    public function fetchTypistList($audioJobId, $removeSingle = false)
	{
		$db = $this->getAdapter();

        $sql = "SELECT group_concat(user_id) FROM {$this->_name} ajt
        INNER JOIN users u on u.id = ajt.user_id
        WHERE audio_job_id = {$audioJobId} ANd current = 1";
		$results = $db->fetchOne($sql);
        $return = explode(',', $results);

        if ($removeSingle && 1 === count($return))
        {
            $this->update(array('current' => 0), "audio_job_id = '" . $audioJobId . "'");
            return array();
        }
        else
        {
            return $return;
        }
	}

	public function fetchByAudioJobId($audioJobId, $singleTypist = false, $order = null, $realOnly = false )
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ajt.audio_job_id = ?', $audioJobId);
		$select->where('ajt.current = 1');

        if ( $realOnly )
        {
            $select->where( 'ajt.user_id > 0' );
        }

        if ( $order )
        {
            $select->order( $order );
        }

        if ($singleTypist)
        {
            $select->limit(1);
            $results = $db->fetchRow($select);
        }
        else
        {
            $results = $db->fetchAll($select);
        }

		return $results;
	}

    /**
     * Fetch an array of sub standard audio job rows
     *
     * @param int $audioJobId Audio Job ID
     *
     * @return array
     */
    public function fetchSubStandardRows( $audioJobId )
    {
        $select = $this->getAdapter()->select();
        $select->from( array( 'ajt' => $this->_name ), array( 'id' ) )
            ->join( array( 'u' => 'users' ), 'u.id = ajt.user_id', array( 'name' ) )
            ->where( 'ajt.audio_job_id = ?', $audioJobId )
            ->where( "ajt.substandard_payrate = '1'" );

        return $this->getAdapter()->fetchAll( $select );
    }

    /**
     * Has substandard liabilities
     *
     * @param int $audioJobId Audio Job ID
     *
     * @return bool
     */
    public function hasSubStandardLiabilities( $audioJobId )
    {
        return (bool) ( count( $this->fetchSubStandardRows( $audioJobId ) ) );
    }

    /**
     * Remove a substandard liability
     *
     * @param int $rowId Audio Job Typist row ID
     *
     * @return bool
     */
    public function removeSubStandard( $rowId )
    {
        $data = array(
            'id'                  => $rowId,
            'substandard_payrate' => '0'
        );
        return (bool) $this->save( $data );
    }

    public function fetchAudioJobsByUserId($userId, $audioJobId = null, $isCurrent = null, $isCompleted = null)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ajt.user_id = ?', $userId);
		$select->where('ajt.current = 1');

        $select->order('26 ASC');
        $select->order('27 DESC');

		// add acl checks
		// if not an admin then restrict access only to authorised users
		if (!self::$_acl->isAdmin()) {
			$select->join(array('acl' => 'acl'), 'aj.acl_resource_id = acl.resource_id ' .
								'AND acl.privilege_id = 18 ' . // list access for audio jobs
								'AND acl.role_id = ' . $this->_getCurrentUserAclRoleId() . ' ' . // current users acl_role_id
								'AND acl.mode = \'allow\''
			, array());
		}

		if (!is_null($audioJobId)) {
			$select->where('ajt.audio_job_id = ?', $audioJobId);

			$where = '';

			if (!is_null($isCurrent) && $isCurrent) {
				$where .= ' (aj.completed = 0';
				if (!is_null($isCompleted) && $isCompleted) {
					$where .=  ' or aj.completed = 1';
				}
				$where .= ')';
			}

			if (!is_null($isCurrent) && !$isCurrent) {
				if ($where != '') {
					$where .= 'and ';
				}
				$where .= ' (aj.completed = 1';
				if (!is_null($isCompleted) && isCompleted) {
					$where .= ' or aj.completed = 1';
				}
				$where .= ')';
			}

			if ($where != '') {
				$select->where($where);
			}

			$results = $db->fetchRow($select);
		} else {
			$where = '';

			if (!is_null($isCurrent) && $isCurrent) {
				$where .= ' (aj.completed = 0';
				if (!is_null($isCompleted) && $isCompleted) {
					$where .=  ' or aj.completed = 1';
				}
				$where .= ')';
			}

			if (!is_null($isCurrent) && !$isCurrent) {
				if ($where != '') {
					$where .= 'and ';
				}
				$where .= ' (aj.completed = 1';
				if (!is_null($isCompleted) && isCompleted) {
					$where .= ' or aj.completed = 1';
				}
				$where .= ')';
			}

			if ($where != '') {
				$select->where($where);
			}

			$results = $db->fetchAll($select);
		}

		return $results;

	}

    const UPDATE_PART     = 0;
    const UPDATE_COMPLETE = 1;
    const UPDATE_FAILED   = 2;

    /**
     * Assign multiple typists
     *
     * @param int   $audioJobId  Audio job ID
     * @param array $typistData  Array containing typist audio job data
     * @param array $subStandard Substandard pay rate info
     *
     * @return bool
     */
    public function assignMultipleTypists( $audioJobId, $typistData, $subStandard = array() )
    {
        $return = array(
            'result' => self::UPDATE_COMPLETE
        );

        $current = $this->fetchAll( $this->_quoteInto( 'audio_job_id = ?', $audioJobId ) );
        foreach ( $current as $typist )
        {
            $typist->current = 0;
            $typist->save();
        }

        $userMapper = new Application_Model_UserMapper();

        foreach ( $typistData as $data )
        {
            if ( empty( $data['user_id'] ) )
            {
                $return['result'] = self::UPDATE_PART;
            }

            if ( empty( $data['id'] ) )
            {
                $row    = $this->createRow();
                $result = $this->_updateTypistRow( $row, $data );
                if ( !empty( $data['user_id'] ) )
                {
                    $return['assigned_user'][] = $result->id;
                    $newUser                   = $userMapper->fetchRow( 'id=' . $data['user_id'] );
                    $this->_insertAcl( $data['audio_job_id'], $newUser['acl_role_id'], 18, 'allow' );
                }
            }
            else
            {
                $row = $this->fetchRow( 'id=' . $data['id'] );
                if ( $row->user_id != $data['user_id'] )
                {
                    $row->current = 0;
                    $newRow       = $this->createRow();

                    if ( is_array( $subStandard ) && count( $subStandard ) > 0 )
                    {
                        if ( in_array( $data['id'], $subStandard ) )
                        {
                            $row->substandard_payrate    = 1;
                            $newRow->replacement_payrate = 1;
                        }
                    }

                    if ( '1' == $row->replacement_payrate )
                    {
                        $newRow->replacement_payrate = '1';
                    }

                    $row->save();
                    $newRow->current = 1;
                    $result          = $this->_updateTypistRow( $newRow, $data );

                    if ( !empty( $row->user_id ) )
                    {
                        $return['removed_user'][] = $row->id;
                        $oldUser                  = $userMapper->fetchRow( 'id=' . $row->user_id );
                        $this->_insertAcl( $data['audio_job_id'], $oldUser['acl_role_id'], 18, 'deny' );
                    }
                    if ( !empty( $data['user_id'] ) )
                    {
                        $return['assigned_user'][] = $result->id;
                        $newUser                   = $userMapper->fetchRow( 'id=' . $data['user_id'] );
                        $this->_insertAcl( $data['audio_job_id'], $newUser['acl_role_id'], 18, 'allow' );
                    }
                }
                else
                {
                    $row->current = 1;
                    $result       = $this->_updateTypistRow( $row, $data );

                    if (!empty( $data['user_id'] ) )
                    {
                        $newUser = $userMapper->fetchRow( 'id = ' . $data['user_id'] );
                        $this->_insertAcl( $data['audio_job_id'], $newUser['acl_role_id'], 18, 'allow' );
                    }
                }
            }
        }
        if ( $result )
        {
            return $return;
        }
        else
        {
            $return['result'] = self::UPDATE_FAILED;
        }
    }

    /**
     * Typist/Proofreader assign audio job part / cancellation email notification
     *
     * @param string $emailTemplate
     * @param int    $audioJobsUserId (audio_jobs_typists|audio_jobs_proofreaders table id)
     *
     * @return void
     */
    protected function _sendAssignedUpdateEmailNotification($emailTemplate, $audioJobsUserId)
    {
        // email notification
        $options = array(
            'emailType' => $emailTemplate,
            'id'        => $audioJobsUserId
        );
        $this->_email->send($this->view, $options);
    }

    protected function _insertAcl($audioJobId, $aclRoleId, $aclPrivilegeId, $mode='deny')
    {

        $audioJobMapper = new Application_Model_AudioJobMapper();
        $audioJob       = $audioJobMapper->fetchById($audioJobId);

        $aclResourceId = $audioJob['acl_resource_id'];
        $aclMapper     = new Application_Model_AclMapper();

        $existing = $aclMapper->fetchByResourceIdRoleIdAndPrivilege($aclResourceId, $aclRoleId, $aclPrivilegeId);
        foreach ($existing as $acl)
        {
            $data         = array();
            $data['id']   = $acl['id'];
            $data['mode'] = $mode;
            $aclMapper->save($data);
        }

        // belt and braces -
        // if we've not updated any acl records previous loop then do it here
        if (count($existing) == 0)
        {
            $aclData = array(
                'role_id'      => $aclRoleId,
                'resource_id'  => $aclResourceId,
                'privilege_id' => $aclPrivilegeId,
                'mode'         => $mode
            );
            $aclMapper->insert($aclData);
        }
    }

    protected function _updateTypistRow( $row, $data )
    {
        foreach ( $data as $key => $value )
        {
            if ( isset( $row[$key] ) && $key != 'id' )
            {
                $row->{$key} = $value;
            }
        }

        if ( !empty( $data['user_id'] ) )
        {
            $row->pay_per_minute = $this->_getPayRate( $data['user_id'], $data['audio_job_id'] );
        }

        $row->created_date    = date('Y-m-d H:i:s');
        $row->created_user_id = $this->_getCurrentUserId();

        $row->save();
        return $row;
    }

    public function fetchUserShiftJobs($userId, $shiftId)
	{
		$db = $this->getAdapter();

        $shiftMapper = new Application_Model_TypistsDefaultShiftMapper();
        $dateRange   = $shiftMapper->getShiftDatRange($shiftId);

        $sql = "SELECT
            IFNULL(`t`.`name`,`s`.`name`) name,
            j.length_seconds,
            ajt.minutes_end,
            ajt.minutes_start
        FROM {$this->_name} ajt
        INNER JOIN audio_jobs j ON j.id = ajt.audio_job_id
        LEFT JOIN lkp_transcription_types t ON j.transcription_type_id IS NOT NULL AND t.id = j.transcription_type_id
        LEFT JOIN s_service s ON j.service_id IS NOT NULL AND s.id = j.service_id
        LEFT JOIN lkp_audio_job_statuses ajs ON j.status_id = ajs.id
        WHERE ajt.user_id = {$userId}
        AND ajt.shift_id = {$shiftId}
        AND ajt.current = '1'
        AND ajs.complete = '0'
        AND ajt.created_date >= '" . $dateRange['start'] . "'
        AND ajt.created_date <= '" . $dateRange['end'] . "'
        GROUP BY ajt.audio_job_id";

		$results = $db->fetchAll($sql);
        return $results;
	}
}

