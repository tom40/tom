<?php
/**
 * Password validation
 *
 * @copyright  Copyright (c) 2012 Take Note
 * @version    $Id$
 * @since      1.0
 */
class App_Validate_Password extends Zend_Validate_Abstract
{
    const TOO_SHORT           = 'stringLengthTooShort';
    const TOO_LONG            = 'stringLengthTooLong';
    const CONTAINS_WHITESPACE = 'containsWhitespace';
    const NO_SPECIAL_CHARS    = 'noSpecialCharacters';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::TOO_SHORT           => "Password is too short; it must be at least %min% characters.",
        self::TOO_LONG            => "Password is too long; it must be no more than %max% characters.",
        self::CONTAINS_WHITESPACE => "Password cannot contain whitespace characters.",
        self::NO_SPECIAL_CHARS    => "Password must contain at least 1 upper case letter, 1 lower case letter and 1 numeral.",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'min' => '_min',
        'max' => '_max'
    );

    /**
     * Minimum length
     *
     * @var integer
     */
    protected $_min;

    /**
     * Maximum length
     *
     * If null, there is no maximum length
     *
     * @var integer|null
     */
    protected $_max;

    /**
     * Encoding to use
     *
     * @var string|null
     */
    protected $_encoding;

    /**
     * Sets validator options
     *
     * @param int    $min
     * @param int    $max OPTIONAL
     * @param string $encoding OPTIONAL
     * @return void
     */
    public function __construct($min = 8, $max = null, $encoding = null)
    {
        $this->setMin($min);
        $this->setMax($max);
        $this->setEncoding($encoding);
    }

    /**
     * Returns the min option
     *
     * @return int
     */
    public function getMin()
    {
        return $this->_min;
    }

    /**
     * Sets the min option
     *
     * @param  int $min
     * @throws Zend_Validate_Exception
     * @return Zend_Validate_StringLength Provides a fluent interface
     */
    public function setMin($min)
    {
        if (null !== $this->_max && $min > $this->_max) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The minimum must be less than or equal to the maximum length, but $min >"
                                            . " $this->_max");
        }
        $this->_min = max(0, (integer) $min);
        return $this;
    }

    /**
     * Returns the max option
     *
     * @return int|null
     */
    public function getMax()
    {
        return $this->_max;
    }

    /**
     * Sets the max option
     *
     * @param  int|null $max
     * @throws Zend_Validate_Exception
     * @return Zend_Validate_StringLength Provides a fluent interface
     */
    public function setMax($max)
    {
        if (null === $max) {
            $this->_max = null;
        } else if ($max < $this->_min) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The maximum must be greater than or equal to the minimum length, but "
                                            . "$max < $this->_min");
        } else {
            $this->_max = (integer) $max;
        }

        return $this;
    }

    /**
     * Returns the actual encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Sets a new encoding to use
     *
     * @param string $encoding OPTIONAL
     * @return Zend_Validate_StringLength
     */
    public function setEncoding($encoding = null)
    {
        if ($encoding !== null) {
            $orig   = iconv_get_encoding('internal_encoding');
            $result = iconv_set_encoding('internal_encoding', $encoding);
            if (!$result) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception('Given encoding not supported on this OS!');
            }

            iconv_set_encoding('internal_encoding', $orig);
        }

        $this->_encoding = $encoding;
        return $this;
    }

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
        $valueString = (string) $value;
        $this->_setValue($valueString);
        if ($this->_encoding !== null) {
            $length = iconv_strlen($valueString, $this->_encoding);
        } else {
            $length = iconv_strlen($valueString);
        }

        if ($length < $this->_min) {
            $this->_error(self::TOO_SHORT);
        }

        if (null !== $this->_max && $this->_max < $length) {
            $this->_error(self::TOO_LONG);
        }

        $pos = strpos($valueString, ' ');
        if ($pos !== false) {
            $this->_error(self::CONTAINS_WHITESPACE);
        }

        // Special characters
        $upperCaseChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowerCaseChars = 'abcdefghijklmnopqrstuvwxyz';
        $numeralChars   = '0123456789';

        $upperCaseCount = 0;
        $lowerCaseCount = 0;
        $numeralCount   = 0;

        for ($i = 0; $i < strlen($valueString); $i++) {

            // Get current character
            $char = mb_substr($valueString, $i, 1);

            // Upper case
            if (strpos($upperCaseChars, $char) !== false) {
                $upperCaseCount++;
            }

            // Lower case
            if (strpos($lowerCaseChars, $char) !== false) {
                $lowerCaseCount++;
            }

            // Upper case
            if (strpos($numeralChars, $char) !== false) {
                $numeralCount++;
            }
        }

        if ($upperCaseCount == 0 || $lowerCaseCount == 0 || $numeralCount == 0) {
            $this->_error(self::NO_SPECIAL_CHARS);
        }

        if (count($this->_messages)) {
            return false;
        } else {
            return true;
        }
    }
}
?>