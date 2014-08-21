<?php

class Application_Model_TypistMapper extends App_Db_Table
{
	protected $_name = 'typists';

    /**
     * Row class name
     * @var string
     */
    protected $_rowClass = 'Application_Model_Typist_Row';

    protected static $abilitiesMap = array (
        'trained_summaries' => 'S',
        'trained_notes'     => 'N',
        'trained_legal'     => 'L',
        'full'              => 'F',
        'note_taker'        => 'NT'
    );

    /**
     * Access the abilities/training map
     * @param null $string
     * @param bool $code
     * @return array
     */
    public static function abilitiesMap( $string = null, $code = false )
    {
        if ( null === $string )
        {
            return self::$abilitiesMap;
        }
        $map = self::$abilitiesMap;
        if ( $code )
        {
            $map = array_flip( self::$abilitiesMap );
        }
        return $map[$string];
    }

	public function makeFetchSelect($db)
	{
		$select = $db->select();

		$select->from(array('t' => 'typists'));
		$select->joinLeft(array('u' 	=> 'users'), 't.user_id = u.id', array('user_id' => 'id', 'user_name' => 'name'));
		$select->joinLeft(array('ltg' => 'lkp_typist_grades'), 't.grade_id = ltg.id', array('typing_grade' => 'name'));

		return $select;

	}

	public function fetchCurrent()
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);
		$select->order('user_name');
		$results = $db->fetchAll($select);

		return $results;
	}

    public function fetchAllTypists()
	{
		$db     = $this->getAdapter();
		$select = $db->select();
		$select->from(array('t' => 'typists'), array());
		$select->joinInner(array('u' => 'users'), 't.user_id = u.id');
		$select->order('u.username');
		$results = $db->fetchAll($select);
		return $results;
	}

    public function fetchGrades()
    {
        $adapter = $this->getAdapter();
        $select  = $adapter->select();
        $select->from(array('lg' => 'lkp_typist_grades'));
        $select->order('lg.sort_order ASC');
        return $adapter->fetchAll($select);

    }

    public function fetchTypistsForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('t' => 'typists'))
            ->joinLeft(array('u' => 'users'), 't.user_id = u.id', array('key' =>'id', 'value' => 'name'))
            ->order('u.username');

		$results = $db->fetchAll($select);
		return $results;
	}

	public function fetchOnOffShift($audioJobId)
	{
		$db = $this->getAdapter();

		// build a temp table with the user_ids of any typists assigned to this audio job
		$sql = 'CREATE TEMPORARY TABLE tmp_audio_typists (`user_id` int(11), KEY `ix_user_id` (`user_id`)) ENGINE=InnoDB CHARACTER SET=utf8';
		$db->query($sql);

		$select = $db->select();
		$select->from(array('ajt' => 'audio_jobs_typists'), array('user_id'));
		$select->where('audio_job_id = ?' , $audioJobId);
		$select->where('current = ?' , 1);
		$select->group('user_id');

		$sql = 'INSERT INTO tmp_audio_typists (user_id) ' . $select->__toString();
		$db->query($sql);

		$select = $this->makeFetchSelect($db);

		$select->join(array('us' => 'users_shifts'), 'u.id = us.user_id', array());
		$select->where('shift_date = ?', date('Y-m-d', time()));

		$today = getdate();
		if ($today['hours'] < 12) {
			$select->where('shift_id = ?', 1);
		} else {
			$select->where('shift_id = ?', 2);
		}
		$results = array();
		$results['onShift'] = $db->fetchAll($select);


		$sql = 'CREATE TEMPORARY TABLE tmp_typists (`user_id` int(11), KEY `ix_user_id` (`user_id`)) ENGINE=InnoDB CHARACTER SET=utf8';
		$db->query($sql);
		$select->reset( Zend_Db_Select::COLUMNS );
		$select->reset( Zend_Db_Select::ORDER );
		$select->columns('user_id');

		$sql = 'INSERT INTO tmp_typists (user_id) ' . $select->__toString();

		$db->query($sql);
		$select = $this->makeFetchSelect($db);

		// exclude typists on shift
		$select->joinLeft(array('tt' => 'tmp_typists'), 'u.id = tt.user_id', array());
		$select->where('tt.user_id is null');

		$select->order('ltg.sort_order desc');

		$results['offShift'] = $db->fetchAll($select);

		$sql = 'DROP TEMPORARY TABLE IF EXISTS tmp_typists';
		$db->query($sql);

		$sql = 'DROP TEMPORARY TABLE IF EXISTS tmp_audio_typists';
		$db->query($sql);

		return $results;
	}

	public function fetchById($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('t.id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}

	public function fetchByUserId($id)
	{
		$db = $this->getAdapter();
		$select = $this->makeFetchSelect($db);

		$select->where('t.user_id = ?', $id);

		$results = $db->fetchRow($select);

		return $results;
	}

	public function fetchAllGradesForDropdown()
	{
		$db = $this->getAdapter();
		$select = $db->select();

		$select->from(array('ltg' => 'lkp_typist_grades'), array('key' =>'id', 'value' => 'name'));
		$select->order('sort_order');

		$results = $db->fetchAll($select);
		return $results;
	}
}