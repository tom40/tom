<?php
class App_Validate_EmailAddress extends Zend_Validate_Abstract
{
	const MSG_INVALID = "'%value%' is not a valid email address";

	/** @var Zend_Validate_EmailAddress */
	protected static $_validatorEmailAddress;

	/** @var array */
	protected $_messageTemplates = array();

	/**
	 *
	 * @return Zend_Validate_EmailAddress
	 */
	public static function getValidatorEmailAddress()
	{
		if (is_null(self::$_validatorEmailAddress)) {
			self::$_validatorEmailAddress = new Zend_Validate_EmailAddress();
		}
		return self::$_validatorEmailAddress;
	}

	/**
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function isValid($value)
	{
		$valid = true;
		if (!self::getValidatorEmailAddress()->isValid($value)) {
			$this->_messageTemplates[$value] = self::MSG_INVALID;
			$this->_error($value, $value);
			$valid = false;
		}
		return $valid;
	}
}
?>