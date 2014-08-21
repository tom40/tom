<?php

class Application_Model_AudioJobProofreaderMapper extends App_Db_Table
{
	protected $_name = 'audio_jobs_proofreaders';

	public function makeFetchSelect($db)
	{
		$select = $db->select();

		$select->from(
            array('ajp' => 'audio_jobs_proofreaders'),
            array(
                '*',
                'due_date_unix'       => 'UNIX_TIMESTAMP(ajp.due_date)',
                'due_days'            => 'DATEDIFF(ajp.due_date, CURDATE())',
                'due_hours'           => 'HOUR(TIMEDIFF(ajp.due_date, NOW()))',
                'proofreader_comment' => 'comment'
            )
        );
		$select->joinLeft(array('u' => 'users'), 'u.id = ajp.user_id', array('proofreader_name' => 'name', 'acl_role_id' => 'acl_role_id'));

		$select->join(array('aj' => 'audio_jobs'),
			'aj.id = ajp.audio_job_id',
			array(
				'job_id',
				'file_name',
				'link',
                'internal_comments',
				'length_seconds',
				'transcription_file_count'	=> 'fn_TranscriptionFileCountByAudioJobId(aj.id)',
				'support_file_count'		=> 'fn_SupportFileCountByJobId(aj.job_id)',
                'typist_count' 				=> 'fn_AudioJobTypistCount(aj.id)',
                'proofreader_count' 		=> 'fn_AudioJobProofreaderCount(aj.id)',
                'lead_sort'                 => 'IF(aj.lead_id < 1 OR aj.lead_id IS NULL, aj.id, aj.lead_id)',
                'is_lead'                   => 'IF(aj.lead_id < 1 OR aj.lead_id IS NULL, 1, 0)',
                'lead_id'
			)
		);

		$select->join(array('uc' => 'users'), 'uc.id = aj.created_user_id', array('created_user_name' => 'name'));
		$select->joinLeft(array('lpf' => 'lkp_priorities'), 'lpf.id = aj.priority_id',
		array(
							'priority_name' 		=> 'name',
							'priority_flag_colour' 	=> 'flag_colour',
							'priority_sort_order' 	=> 'sort_order'
		)
		);

		$select->join(array('lajs' => 'lkp_audio_job_statuses'), 'lajs.id = aj.status_id', array('status' => 'name'));

        // Display client
        $select->join(array('j' => 'jobs'), 'aj.job_id = j.id', array());
		$select->join(array('c' => 'clients'), 'j.client_id = c.id', array('client_name' => 'name', 'client_id' => 'id'));

		$select->where('aj.archived = 0');
        $select->where('aj.deleted IS NULL');
		return $select;
	}

    /**
     * Check if audio job is split and has proofreader assigned
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
     * Returns the number of proofreaders assigned to the audio job
     * ignoring any records kept for the sake of keeping split file timings
     *
     * @param int $audioJobId
     *
     * @return int
     */
    public function getAssignedProofreadersCount($audioJobId)
    {
        $db = $this->getAdapter();
		$sql = "SELECT count(*) FROM {$this->_name} WHERE audio_job_id = {$audioJobId} AND current = 1 AND user_id <> 0";
        $count = $db->fetchOne($sql);
		return $count;
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
        $select->joinInner(array('ajp' => $this->_name), 'ajp.user_id = tf.created_user_id AND ajp.audio_job_id = tf.audio_job_id', array());
        $select->where('tf.audio_job_id = ?', $audioJobId);
        if (!empty($createdUserId))
        {
            $select->where('tf.created_user_id = ?', $createdUserId);
        }

        $select->where('tf.archived = 0');
        $select->where('ajp.current = 1');
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

    /**
     * Check if the given user is a proofreader for the specified audio job
     *
     * @param int $userId the user to check in the audio job
     * @param int $jobId the audio to check the proofreader in
     *
     * @return bool
     */
    public function isAudioJobProofreader($userId, $audioJobId)
    {
        $proofreader = $this->fetchAudioJobsByUserId($userId, $audioJobId);
        if (!empty($proofreader))
        {
            return true;
        }

        return false;
    }

	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ajp.id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}

    public function fetchByIdWithTranscriptionType($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
        $select->joinLeft(array('ttype' => 'lkp_transcription_types'), 'ttype.id = aj.transcription_type_id', array('transcription_type' => 'name'));

		$select->where('ajp.id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}

    public function fetchByUserAndAudioJob($userId, $audioJobId)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ajp.audio_job_id = ?', $audioJobId);
		$select->where('ajp.user_id = ?', $userId);
 		$select->where('ajp.current = ?', 1);

		$results = $db->fetchRow($select);
		return $results;
	}

    /**
     * Return an array of proofreader IDs
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
    public function fetchProofreaderList($audioJobId, $removeSingle = false)
	{
		$db = $this->getAdapter();

        $sql = "SELECT group_concat(user_id) FROM {$this->_name} ajt WHERE audio_job_id = {$audioJobId} AND current = 1";
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

	public function fetchByAudioJobId($audioJobId, $single = false)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ajp.audio_job_id = ?', $audioJobId);
		$select->where('ajp.current = ?', 1);

        if ($single)
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

	public function fetchAudioJobsByUserId($userId, $audioJobId = null, $isCurrent = null, $isCompleted = null)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('ajp.user_id = ?', $userId);
		$select->where('ajp.current = 1');

        $select->order('28 ASC');
        $select->order('29 DESC');

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
			$select->where('ajp.audio_job_id = ?', $audioJobId);

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

    public function fetchUserShiftJobs($userId, $shiftId)
	{
		$db = $this->getAdapter();

        $shiftMapper = new Application_Model_ProofreadersDefaultShiftMapper();
        $dateRange   = $shiftMapper->getShiftDatRange($shiftId);

        $sql = "SELECT
            t.name,
            j.length_seconds,
            ajt.minutes_end,
            ajt.minutes_start
        FROM {$this->_name} ajt
        INNER JOIN audio_jobs j ON j.id = ajt.audio_job_id
        INNER JOIN lkp_transcription_types t ON t.id = j.transcription_type_id
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

