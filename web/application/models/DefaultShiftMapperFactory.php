<?php

/**
 * Returns the correct default shift mapper
 *
 */
class Application_Model_DefaultShiftMapperFactory extends App_Db_Table
{
    public static function getObject($userType)
    {
        if ($userType == Application_Model_UsersShiftMapper::TYPIST_SHIFT)
        {
            return new Application_Model_TypistsDefaultShiftMapper();
        }
        else if ($userType == Application_Model_UsersShiftMapper::PROOFREADER_SHIFT)
        {
            return new Application_Model_ProofreadersDefaultShiftMapper();
        }
    }
}