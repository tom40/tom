<?php

class Application_Model_TurnaroundTimeMapper extends App_Db_Table
{

    const TT_7DAYS     = 1;
    const TT_48HR      = 2;
    const TT_24HR      = 3;
    const TT_SAMEDAY   = 4;
    const TT_OVERNIGHT = 5;
    const TT_36HR      = 6;

    const OFFICE_CLOSE_HR   = 17;
    const OFFICE_OPEN_HR    = 9;
    const OFFICE_OPEN_EARLY = 5;
    const SAME_DAY_START    = 10;
    const SAME_DAY_END      = 18;
    const OVERNIGHT         = 22;
    /**
     * Table name
     * @var string
     */
    protected $_name = 'lkp_turnaround_times';

    /**
     * @param $db
     * @param int $current
     * @return mixed
     */
    protected $_dueDateMethods = array(
        self::TT_7DAYS     => 'Week',
        self::TT_48HR      => 'TwoDays',
        self::TT_24HR      => 'Day',
        self::TT_SAMEDAY   => 'SameDay',
        self::TT_OVERNIGHT => 'OverNight',
        self::TT_36HR      => 'Summary',
    );

    public function makeFetchSelect($db, $current=1)
    {
        $select = $db->select();
        $select->from(array('ltt' => 'lkp_turnaround_times'));

        return $select;
    }

    public function fetchById($id)
    {
        $db = $this->getAdapter();
        $select = $this->makeFetchSelect($db);

        $select->where('ltt.id = ?', $id);

        $results = $db->fetchRow($select);

        return $results;
    }

    public function fetchAllForDropdown()
    {
        $db = $this->getAdapter();
        $select = $db->select();

        $select->from(array('ltt' => 'lkp_turnaround_times'), array('key' =>'id', 'value' => 'name'));
        $select->order('sort_order');

        $results = $db->fetchAll($select);
        return $results;
    }

    /**
     * Calculate audio job due date based on turnaround time
     *
     * @param int  $taId      Turn around time ID
     * @param int  $fromUnix  If null, uses time()
     * @param bool $isService If false, use original transcription times
     *
     * @return string
     */
    public function calculateDueDate($taId, $fromUnix = null, $isService = false )
    {
        if (null === $fromUnix)
        {
            $fromUnix = time();
        }

        if ( $isService )
        {
            $model           = new Application_Model_TurnaroundTime();
            $turnaoroundTime = $model->fetchRow( 'id = ' . $taId );
            $method          = '_calculate' . $turnaoroundTime->period_method;
        }
        else
        {
            $method = '_calculate' . $this->_dueDateMethods[$taId];
        }
        return $this->{$method}($fromUnix);
    }

    /**
     * Calculate 12 hours from audio job creation
     *
     * @param int $fromUnix Unix time stamp
     *
     * @return string
     */
    protected function _calculateHalfDay( $fromUnix )
    {
        $current = new DateTime();
        $hour    = $current->format('H');

        if ( $hour < self::OFFICE_CLOSE_HR && $hour >= self::OFFICE_OPEN_EARLY )
        {
            $date = new DateTime( date( 'Y-m-d H:i:s', $fromUnix ) );
            $date->add( new DateInterval( 'PT12H' ) );
        }
        elseif ( $hour < self::OFFICE_OPEN_EARLY )
        {
            $date = new DateTime( date( 'Y-m-d', $fromUnix ) . " 05:00:00" );
            $date->add( new DateInterval( 'PT12H' ) );
        }
        else
        {
            $date = new DateTime( date( 'Y-m-d', $fromUnix ) . " 05:00:00" );
            $date->add( new DateInterval( 'P1DT12H' ) );
        }

        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Calculate 7 day turnaround
     *
     * @param int $fromUnix Unix time stamp
     *
     * @return string
     */
    protected function _calculateWeek($fromUnix)
    {
        $current = new DateTime();
        $date    = new DateTime(date('Y-m-d', $fromUnix) . " 17:00:00");
        $hour    = $current->format('H');
        if ($hour < self::OFFICE_CLOSE_HR)
        {
            $date->add(new DateInterval('P7D'));
        }
        else
        {
            $date->add(new DateInterval('P8D'));
        }
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Calculate 48hr turnaround
     *
     * @param int $fromUnix Unix time stamp
     *
     * @return string
     */
    protected function _calculateTwoDays($fromUnix)
    {
        $current = new DateTime();
        $hour    = $current->format('H');
        if ($hour < self::OFFICE_CLOSE_HR)
        {
            if ($hour < self::OFFICE_OPEN_HR)
            {
                $date = new DateTime(date('Y-m-d', $fromUnix) . " 09:00:00");
            }
            else
            {
                $date = new DateTime(date('Y-m-d H:i:s', $fromUnix));
            }
            $date->add(new DateInterval('P2D'));
        }
        else
        {
            $date = new DateTime(date('Y-m-d', $fromUnix) . " 09:00:00");
            $date->add(new DateInterval('P3D'));
        }
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Calculate 48hr turnaround
     *
     * @param int $fromUnix Unix time stamp
     *
     * @return string
     */
    protected function _calculateDay($fromUnix)
    {
        $current = new DateTime();
        $date    = new DateTime(date('Y-m-d', $fromUnix) . " 17:00:00");
        $hour    = $current->format('H');
        if ($hour < self::OFFICE_CLOSE_HR)
        {
            if ($hour < self::OFFICE_OPEN_HR)
            {
                $date = new DateTime(date('Y-m-d', $fromUnix) . " 09:00:00");
            }
            else
            {
                $date = new DateTime(date('Y-m-d H:i:s', $fromUnix));
            }
            $date->add(new DateInterval('P1D'));
        }
        else
        {
            $date = new DateTime(date('Y-m-d', $fromUnix) . " 09:00:00");
            $date->add(new DateInterval('P2D'));
        }
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Calculate 48hr turnaround
     *
     * @param int $fromUnix Unix time stamp
     *
     * @return string
     */
    protected function _calculateSameDay($fromUnix)
    {
        $current = new DateTime();
        $hour    = $current->format('H');
        if ($hour < self::SAME_DAY_START)
        {
            $date = new DateTime(date('Y-m-d', $fromUnix) . " 18:00:00");
        }
        elseif ($hour >= self::SAME_DAY_START && $hour < self::SAME_DAY_END)
        {
            $date = new DateTime(date('Y-m-d', $fromUnix) . " 09:00:00");
            $date->add(new DateInterval('P1D'));
        }
        else
        {
            $date = new DateTime(date('Y-m-d', $fromUnix) . " 18:00:00");
            $date->add(new DateInterval('P1D'));
        }
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Calculate 48hr turnaround
     *
     * @param int $fromUnix Unix time stamp
     *
     * @return string
     */
    protected function _calculateOvernight($fromUnix)
    {
        $current = new DateTime();

        $hour = $current->format('H');
        if ($hour < self::OVERNIGHT)
        {
            $date = new DateTime(date('Y-m-d', $fromUnix) . " 10:00:00");
        }
        else
        {
            $date = new DateTime(date('Y-m-d', $fromUnix) . " 17:00:00");
        }
        $date->add(new DateInterval('P1D'));
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Calculate 48hr turnaround
     *
     * @param int $fromUnix Unix time stamp
     *
     * @return string
     */
    protected function _calculateSummary($fromUnix)
    {
        $date = new DateTime(date('Y-m-d H:i:s', $fromUnix));
        $date->add(new DateInterval('PT36H'));
        return $date->format('Y-m-d H:i:s');
    }
}

