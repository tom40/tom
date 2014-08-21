<?php

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @category   App
 * @package    App_Validate
 * @copyright  Copyright (c) 2011 One Result Ltd. (http://www.oneresult.co.uk)
 * @version    $Id: Passphrase.php 100 2011-03-15 14:49:27Z ianmunday $
 */
class App_Validate_Passphrase extends Zend_Validate_Abstract
{
    const DEFAULT_ERROR = 'invalidCharacters';
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::DEFAULT_ERROR => 'Please enter only valid characters as the passphrase'
    );

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the string length of $value is at least the min option and
     * no greater than the max option (when the max option is not null).
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if ($this->_isAlphaNumeric($value))
        {
            return true;
        }

        $this->_error(self::DEFAULT_ERROR);
        return false;
    }

    /**
     * Alphanumeric checker
     *
     * @param string $str the string to check
     *
     * @return bool
     */
    protected function _isAlphaNumeric($str)
    {
        if (preg_match("/^[A-Za-z0-9]+$/i", $str))
        {
            return true;
        }

        return false;
    }

}
