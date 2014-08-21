<?php
class App_Filter_CommaSpaceSeparated implements Zend_Filter_Interface
{
	/**
	 *
	 * @param string $value
	 * @return string
	 */
	public function filter($value)
	{
		// normalize delimiters
		$value = str_replace(array(' ', ';'), ',', $value);
		// explode values
		$values = explode(',', $value);
		// remove empty values
		$values = array_filter($values);
		// implode
		$value = implode(', ', $values);
		// return filtered value
		return $value;
	}
}
?>