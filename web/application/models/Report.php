<?php

/**
 * @todo: needs re-factoring and checking!
 */
class Application_Model_Report extends App_Db_Table
{
    protected $_name            = 'report';
    protected $_reportDataTable = 'report_data';
    protected $_criteriaTable   = 'report_criteria';

    public function fetchAreaForDropDown()
    {
        $db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('rc' => 'report_criteria'), array('key' =>'id', 'value' => 'area'))
        ->order('rc.id');

		$results = $db->fetchAll($select);
		return $results;
    }

    public function fetchAllReports()
    {
        $db = $this->getAdapter();
		$select = $db->select();
        $select->from(array('r' => 'report'))
               ->joinInner(array('j' => 'audio_jobs'), 'j.id = r.audio_job_id', array('file_name'))
               ->joinInner(array('u' => 'users'), 'u.id = r.typist_id', array('typist_name' => 'name'))
        ->order('r.date_created');

		$results = $db->fetchAll($select);
		return $results;
    }

    public function getTypistReport($typistId, $audioJobId, $createNew = false)
    {
        $db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('r' => 'report'), array('id'))
               ->where('typist_id = ?', $typistId)
               ->where('audio_job_id = ?', $audioJobId);

		$reportId = $db->fetchOne($select);
        if (empty($reportId))
        {
            if ($createNew)
            {
                $data = array();
                $data['typist_id']    = $typistId;
                $data['audio_job_id'] = $audioJobId;
                $data['date_created'] = date('Y-m-d H:i:s');
                $reportId = $this->insert($data);
            }
        }
		return $reportId;
    }

    public function fetchReport($reportId)
    {
        $db = $this->getAdapter();
		$select = $db->select();
        $select->from(array('r' => 'report'))
               ->joinInner(array('j' => 'audio_jobs'), 'j.id = r.audio_job_id', array('file_name'))
               ->joinInner(array('u' => 'users'), 'u.id = r.typist_id', array('typist_name' => 'name'))
               ->where('r.id = ?', $reportId)
        ->order('r.date_created');

		$results = $db->fetchRow($select);
		return $results;
    }

    /**
     * Check if the user report is completed
     *
     * @param  int $reportId the report id
     *
     * @return bool
     */
    public function isReportCompleted($reportId)
    {
        $db       = $this->getAdapter();
        $select   = $db->select();
        $select->from(array('r' => $this->_name), array('complete'))
               ->where('id = ?', $reportId);
        $complete = $db->fetchOne($select);
        if (true == $complete)
        {
            return true;
        }

        return false;
    }

    /**
     * Set report as complete with ability to unset
     *
     * @param int $reportId
     * @param int $complete
     *
     * @return bool
     */
    public function setReportComplete($reportId, $complete = 1)
    {
        $this->update(array('complete' => $complete), 'id = ' . $reportId);
    }

    public function getCriteria($reportId)
    {
        $db     = $this->getAdapter();
		$select = $db->select();
        $select->from(array('c' => $this->_criteriaTable))
            ->joinLeft(array('rd' => $this->_reportDataTable), "rd.criteria_id = c.id AND rd.report_id = {$reportId}", array('report_id', 'criteria_id', 'userScore' => 'score', 'comment'))
            ->joinLeft(array('r' => $this->_name), 'r.id = rd.report_id', array())
            ->joinLeft(array('j' => 'audio_jobs'), 'j.id = r.audio_job_id', array('file_name'))
            ->joinLeft(array('u' => 'users'), 'u.id = r.typist_id', array('typist_name' => 'name'))
            ->group('c.id');
		return $db->fetchAll($select);
    }

    public function fetchReportById($id)
    {
        $db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('r' => 'report'), array('id', 'audio_job_id', 'date_created', 'typist_id'))
            ->joinInner(array('rd' => 'report_data'), 'rd.report_id = r.id', array('score', 'comment'))
        ->order('r.id');

		$results = $db->fetchRow($select);
		return $results;
    }

    public function reportExists($typistId, $audioJobId)
    {
        $db = $this->getAdapter();
        $select = $db->select();
        $select->from(array('r' => $this->_name))
            ->where('typist_id = ?', $typistId)
            ->where('audio_job_id = ?', $audioJobId);
        $record = $db->fetchRow($select);
        if (!empty($record))
        {
            return true;
        }

        return false;
    }

    public function updateReportSummary($reportId, $comment)
    {
        $db = $this->getAdapter();
        $select = $db->select();
        $select->from(array('r' => $this->_reportDataTable), array('userScore' => 'score'))
            ->joinInner(array('c' => $this->_criteriaTable), 'c.id = r.criteria_id')
            ->where('r.report_id = ?', $reportId);
        $reportData = $db->fetchAll($select);

        $score = 0.00;
        foreach($reportData as $report)
        {
            $score += $report['userScore'];
        }

        $data = array();
        $data['total_score'] = $score;
        $data['comment']     = $comment;
        $this->update($data, 'id = ' . $reportId);
    }

    public static $maxScore;
    public function getMaxScore()
    {
        if (!empty(self::$maxScore))
        {
            return self::$maxScore;
        }

        $db     = $this->getAdapter();
        $select = $db->select();
        $select->from(array('r' => $this->_criteriaTable));
        $results  = $db->fetchAll($select);
        $maxScore = 0.00;
        foreach($results as $result)
        {
           $maxScore += $result['score'];
        }

        self::$maxScore = $maxScore;
        return $maxScore;
    }

    public function isValidScore($criteriaId, $score)
    {
        $db     = $this->getAdapter();
        $select = $db->select();
        $select->from(array('r' => $this->_criteriaTable))
            ->where('r.id = ? ', $criteriaId);
        $result = $db->fetchRow($select);
        $maxScore = $result['score'];
        if ($score > $maxScore)
        {
            return false;
        }

        return true;
    }

    public function createReport($typistId, $audioJobId)
    {
        $data['typist_id']    = $typistId;
        $data['audio_job_id'] = $audioJobId;
        $data['date_created'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function saveReportData($reportId, $criteriaId, $data)
    {//var_dump($data);exit;
        $db = $this->getAdapter();
        $select = $db->select();
        $select->from(array('rd' => $this->_reportDataTable))
            ->where('rd.report_id = ?', $reportId)
            ->where('rd.criteria_id = ?', $criteriaId);
        $record = $db->fetchAll($select);
        if (!empty($record))
        {
            $db->update($this->_reportDataTable, $data, 'report_id = '. $reportId . ' AND criteria_id = ' . $criteriaId);
        }
        else
        {
            $data['report_id'] = $reportId;
            $data['criteria_id'] = $criteriaId;
            $db->insert($this->_reportDataTable, $data);
        }
    }
}

