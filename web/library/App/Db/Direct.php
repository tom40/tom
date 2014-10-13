<?php

/**
 * Parent class for models that access the database directly rather than using the table object
 *
 * @category   App
 * @package    App_Db
 * @subpackage Direct
 * @copyright  Copyright (c) 2012 Take Note
 */
class App_Db_Direct
{
	/**
	 * Return an instance of the database adaptor
	 * 
	 * @return	Zend database adaptor basedo n bootstrap	
	 */
	protected function getAdaptor()
	{
		return Zend_Db_Table::getDefaultAdapter();;
	}
	
	/**
	 * Convert a date into sql format
	 */
	protected function dateInputToSql($date)
	{
		if(!empty($date))
		{
			$date = str_replace( "/", "-", $date );
			$date = date( 'Y-m-d', strtotime( $date ) );
		}	
		
		return $date;
	}
	
	/**
	 * Convert a date to a start of day date time
	 */
	protected function dateAddDayStartTime($date)
	{
		if(!empty($date) && strpos($date, " ")===false)
		{
			$date .= " 00:00:00";
		}
			
		return $date; 
	}
	

	/**
	 * Convert a date to an end of day date time
	 */
	protected function dateAddDayEndTime($date)
	{
		if(!empty($date) && strpos($date, " ")===false)
		{
			$date .= " 23:59:59";
		}
			
		return $date;
	}	
}
