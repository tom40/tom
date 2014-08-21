<?php
class App_Validate_EmailAddresses extends App_Validate_EmailAddress
{
	/**
	 * Values passed in here should be filterd by App_Filter_CommaSpaceSeparated first
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function isValid($value)
	{
		$valid = true;
		$emails = explode(', ', $value);
		foreach ($emails as $email) {
			if (!parent::isValid($email)) {
				$valid = false;
			}
		}
		return $valid;
	}
}
?>