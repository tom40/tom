<?php

class Application_Model_ServiceSpeakerNumber_Row extends Zend_Db_Table_Row
{
    /**
     * Speaker number object
     * @var Application_Model_SpeakerNumber_Row
     */
    protected $_speakerNumber;

    /**
     * Get Service
     *
     * @return Application_Model_SpeakerNumber_Row
     */
    public function getSpeakerNumber()
    {
        if ( null === $this->_speakerNumber )
        {
            $mapper               = new Application_Model_SpeakerNumber();
            $this->_speakerNumber = $mapper->fetchRow( $mapper->select()->where( 'id = ?', $this->speaker_number_id ) );
        }
        return $this->_speakerNumber;
    }
}