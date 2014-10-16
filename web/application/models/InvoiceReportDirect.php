<?php

/**
 * Direct database access to perform report queries quickly
 * 
 * This model implements a fast-lan reader pattern. The role of this pattern 
 * is to go directly to the database to return the data that is required without
 * going through all of the logical entities for each row. This class returns 
 * the data required for the invoice download report. 
 * 
 * @category    Application
 *  
 * @package     Application_Model
 */
class Application_Model_InvoiceReportDirect extends App_Db_Direct
{

    /**
     * Run the custom query to generate the invoice export report.
     * The data has
     * to be iterated to fill in some of the more complex data and also to add
     * sub totals etc
     *
     * @param string $startDate
     *            in the format dd/mm/yyyy
     * @param string $endDate
     *            in the format dd/mm/yyyy
     * @param string $clientId
     *            for a specific client (optional)
     * @param string $statusId
     *            for a specific status (optional)
     *            
     * @return array of the data with the first row containing column names
     */
    public function invoiceExport ($startDate, $endDate, $clientId = null, 
            $statusId = null)
    {
        $db = $this->getAdaptor();
        $sql = null;
        $stmt = null;
        $headings = null;
        $mainData = null;
        $mainDataCount = null;
        $returnData = array();
        $tempData = array();
        $adHocData = array();
        $prevRow = null;
        $billingData = array();
        $whereClause = "";
        $nestedWhereClause = "";
        $runningSubTotal = 0;
        $runningGrandTotal = 0;
        $totalCol = "total_cost";
        $subTotalCol = "discount";
        $subTotalText = "SUB TOTAL";
        $grandTotalText = "REPORT TOTAL";
        $grandTotalRow = null;
        $adHocText = "AD HOC";
        
        // Set up the array of arguments. Note that the dates are needed three
        // times for efficiency
        if (!empty($startDate))
        {
            $arg = $this->dateAddDayStartTime($this->dateInputToSql($startDate));
            $whereClause .= "AND j.job_start_date >= " . $db->quote($arg) . " ";
            $nestedWhereClause .= "AND ids_j.job_start_date >= " .
                     $db->quote($arg) . " ";
        }
        if (!empty($endDate))
        {
            $arg = $this->dateAddDayEndTime($this->dateInputToSql($endDate));
            $whereClause .= "AND j.job_start_date <= " . $db->quote($arg) . " ";
            $nestedWhereClause .= "AND ids_j.job_start_date <= " .
                     $db->quote($arg) . " ";
        }
        if (!empty($clientId))
        {
            $whereClause .= "AND j.client_id = " . $db->quote($clientId) . " ";
            $nestedWhereClause .= "AND ids_j.client_id = " .
                     $db->quote($clientId) . " ";
        }
        if (!empty($statusId))
        {
            $whereClause .= "AND j.status_id = " . $db->quote($statusId) . " ";
            $nestedWhereClause .= "AND ids_j.status_id = " .
                     $db->quote($statusId) . " ";
        }
        
        // First load any ad hoc charges that will need to be added to the job
        // charges
        $sql = "SELECT 	j.id _job_id,
					je.description description,
					je.price price
				FROM jobs AS j
				INNER JOIN jobs_extras AS je ON je.job_id = j.id
				WHERE j.deleted IS NULL
				{$whereClause}";
        
        $stmt = new Zend_Db_Statement_Mysqli($db, $sql);
        $stmt->execute();
        
        // Loop through the ad hoc charges an index against job ID so they can
        // be easily referenced
        while ($row = $stmt->fetch())
        {
            $jobId = $row["_job_id"];
            
            if (!array_key_exists($jobId, $adHocData))
            {
                $adHocData[$jobId] = array();
            }
            
            // Lump extras together under their job id
            $adHocData[$jobId][] = $row;
        }
        
        // Execute the query to load the majority ofthe required data then loop
        // through to
        $sql = "SELECT 	aj.id audio_job_id,
						j.po_number po_number,
						j.title job_title,
						ljs.name job_status,
						DATE_FORMAT( j.job_start_date, '%d.%m.%Y' ) start_date,
						DATE_FORMAT( aj.created_date, '%d.%m.%Y' ) upload_date,	
						c.name `client`,
						u.name contact_name,
						aj.file_name file_name,
						ssg.name service_groups,
						COALESCE( ss.name, ltt.name ) `type`,
						spm.name additional,
						COALESCE( stt.name, ltt2.name )  turnaround,
						COALESCE( snt.name, lsn.name )  speaker_numbers,
						DATE_FORMAT( aj.client_due_date, '%d.%m.%Y' ) file_due_date,
						DATE_FORMAT( j.job_due_date, '%d.%m.%Y' ) project_due_date,
						'-' for_billing,
						'-' billing_rate,
						'-' per_min_cost,
						'-' sub_total,
						'-' project_discount,
						CONCAT( IF(aj.poor_audio>0, (aj.poor_audio-1)*100, '0'), '%' ) poor_audio,
						CONCAT( aj.audio_job_discount, '%') discount,
						'-' total_cost,				
						j.id _job_id,		
						c.id _client_id,
						COALESCE(ids.lead_id, ids.audio_job_id) _audio_or_lead_id,						
						ids.lead_id _lead_id,
						ids.lead_sort _lead_sort,
						aj.service_id _audio_job_service_id,	
						aj.length_seconds _length_seconds,							
						aj.rate _rate,							
						aj.audio_job_discount _audio_job_discount,
						j.discount _job_discount,
						c.discount _client_discount,
						aj.poor_audio _poor_audio,
						SUM(asrv.price) _total_additional_services_price,	
						COUNT( scsg.service_group_id ) _service_group_count,
						COUNT( ajpm.id ) _price_modifier_count
					FROM jobs AS j
					INNER JOIN ( SELECT 	0 lead_sort,
											ids_j.id job_id,
											ids_aj.id audio_job_id,
											NULL lead_id
									FROM jobs AS ids_j
									INNER JOIN audio_jobs AS ids_aj ON ids_aj.job_id = ids_j.id
									WHERE  ids_j.deleted IS NULL
									AND ids_aj.deleted IS NULL
									AND ( ids_aj.lead_id IS NULL OR ids_aj.lead_id = 0 )
									{$nestedWhereClause}
									UNION
									SELECT 	1 lead_sort,
											ids_j.id job_id,
											ids_aj2.id audio_job_id,
											ids_aj2.lead_id lead_id
									FROM jobs AS ids_j
									INNER JOIN audio_jobs AS ids_aj ON ids_aj.job_id = ids_j.id
									INNER JOIN audio_jobs AS ids_aj2 ON ids_aj2.lead_id = ids_aj.id
									WHERE ids_j.deleted IS NULL
									AND ids_aj.deleted IS NULL
									{$nestedWhereClause} ) AS ids ON ids.job_id = j.id
					INNER JOIN audio_jobs AS aj ON aj.id = ids.audio_job_id
					LEFT OUTER JOIN lkp_job_statuses AS ljs ON ljs.id = j.status_id
					LEFT OUTER JOIN clients AS c ON c.id = j.client_id
					LEFT OUTER JOIN users AS u ON u.id = j.primary_user_id
					LEFT OUTER JOIN s_client_service_group AS scsg ON scsg.client_id = c.id
					LEFT OUTER JOIN s_service_group AS ssg ON ssg.id = scsg.service_group_id
					LEFT OUTER JOIN s_service AS ss ON ss.id = aj.service_id
					LEFT OUTER JOIN lkp_transcription_types AS ltt ON ltt.id = aj.transcription_type_id
					LEFT OUTER JOIN audio_jobs_price_modifiers AS ajpm ON ajpm.audio_job_id = aj.id
						AND aj.service_id IS NOT NULL
					LEFT OUTER JOIN s_service_price_modifier AS sspm ON sspm.id = ajpm.service_price_modifier_id
					LEFT OUTER JOIN s_price_modifier AS spm ON spm.id = sspm.price_modifier_id
					LEFT OUTER JOIN s_turnaround_time AS stt ON stt.id = aj.turnaround_time_id
						AND aj.service_id IS NOT NULL
					LEFT OUTER JOIN lkp_turnaround_times AS ltt2 ON ltt2.id = aj.turnaround_time_id
					LEFT OUTER JOIN s_speaker_number AS snt ON snt.id = aj.speaker_numbers_id
						AND aj.service_id IS NOT NULL
					LEFT OUTER JOIN lkp_speaker_numbers AS lsn ON lsn.id = aj.speaker_numbers_id
					LEFT OUTER JOIN additional_services_audio_jobs AS asaj ON asaj.audio_job_id = aj.id
					LEFT OUTER JOIN additional_services AS asrv ON asrv.id = asaj.service_id
					WHERE j.deleted IS NULL
					{$whereClause}
					GROUP BY aj.id
					ORDER BY _job_id, 
							_audio_or_lead_id, 
							_lead_sort, 
							audio_job_id";
        
        $stmt = new Zend_Db_Statement_Mysqli($db, $sql);
        $stmt->execute();
        $mainData = $stmt->fetchAll();
        $mainDataCount = count($mainData);
        
        // Loop through the data tidying up and filling in any missing items
        //
        // $row - is the original retrieved row
        // $csvRow - is the adjusted row with only the required columns for the
        // output
        if ($mainDataCount > 0)
        {
            for ($i = 0; $i < $mainDataCount; $i ++)
            {
                $row = $mainData[$i];
                $isLastRow = ($i == ($mainDataCount - 1));
                $jobId = $row["_job_id"];
                $nextJobId = (!$isLastRow ? $mainData[$i + 1]["_job_id"] : -1);
                $csvRow = $row;
                $parentId = $row["_audio_or_lead_id"];
                
                // Remove the unwanted columns to create the correct result size
                foreach ($csvRow as $csvKey => $csvVal)
                {
                    if ($csvKey{0} == "_")
                    {
                        unset($csvRow[$csvKey]);
                    }
                }
                
                // Store the headings, column counts and positions once
                if (!isset($headings))
                {
                    $headings = array_keys($csvRow);
                    $colCount = count($csvRow);
                }
                
                // Add column names to the final array and store the data in a
                // temporary one
                if (empty($returnData))
                {
                    $returnData[] = $headings;
                }
                
                // PENDING FIX: Check original code for how this id is used
                // $row["_audio_job_service_id"];
                
                // Check if we have more than one service group and so need more
                // descriptions
                if ($row["_service_group_count"] > 1)
                {
                    $csvRow["service_groups"] = $this->get_multi_client_service_groups(
                            $row["_client_id"]);
                }
                
                // Check if we have more than one price modifier and so need
                // more descriptions
                if ($row["_price_modifier_count"] > 1)
                {
                    $csvRow["additional"] = $this->get_multi_price_modifier_names(
                            $row["audio_job_id"]);
                }
                
                // Push the row into the temporary data store
                $tempData[] = $csvRow;
                
                // If this isn't the first row, and the job is different, then
                // add ad hoc charges and the subtotal row
                if ($jobId !== $nextJobId)
                {
                    $subTotalRow = null;
                    
                    // Add Ad Hoc rows retrieved previously
                    if (array_key_exists($jobId, $adHocData))
                    {
                        foreach ($adHocData[$jobId] as $adHocRow)
                        {
                            $adHocTemp = $this->flip_array_to_empty($headings);
                            $adHocTemp[$totalCol] = $adHocRow["price"];
                            $adHocTemp[$subTotalCol] = $adHocText;
                            $tempData[] = $adHocTemp;
                        }
                    }
                    
                    // Put a sub total text in penultimate column
                    $subTotalRow = $this->flip_array_to_empty($headings);
                    $subTotalRow[$subTotalCol] = $row["po_number"] . " " .
                             $subTotalText;
                    
                    // Add to csv data
                    $tempData[] = $subTotalRow;
                }
                
                // Store and increment the billing data items so we can group
                // child rows with their
                // parents in the next loop
                if (empty($billingData[$parentId]))
                {
                    $billingData[$parentId] = array(
                            "_length_seconds" => 0,
                            "_rate" => $row["_rate"],
                            "_total_additional_services_price" => $row["_total_additional_services_price"],
                            "_audio_job_discount" => $row["_audio_job_discount"],
                            "_poor_audio" => $row["_poor_audio"],
                            "_job_discount" => $row["_job_discount"],
                            "_client_discount" => $row["_client_discount"]);
                }
                
                // Add all seconds to itself or if a child record add to parent
                $billingData[$parentId]["_length_seconds"] += $row["_length_seconds"];
                
                // Store for next row
                $prevRow = $row;
            }
            
            // Add a grand total row at the end
            $grandTotalRow = $this->flip_array_to_empty($headings);
            $grandTotalRow[$subTotalCol] = $grandTotalText;
            $tempData[] = $grandTotalRow;
        }
        
        // Run through the data one more time to create sub totals and billings
        foreach ($tempData as $tempRow)
        {
            $subTotalColVal = !empty($tempRow[$subTotalCol]) ? $tempRow[$subTotalCol] : null;
            $audioId = !empty($tempRow["audio_job_id"]) ? $tempRow["audio_job_id"] : null;
            
            if (!empty($subTotalColVal) &&
                     strpos($subTotalColVal, $grandTotalText) !== false)
            {
                // This is a sub total row so take the total of all sub totals
                // and set
                $tempRow[$totalCol] = "£" . number_format($runningGrandTotal, 2);
            }
            else if (!empty($subTotalColVal) &&
                     strpos($subTotalColVal, $subTotalText) !== false)
            {
                // This is a sub total row so take the total of all sub totals 
                // and set
                $tempRow[$totalCol] = "£" . number_format($runningSubTotal, 2);
                $runningSubTotal = 0;
            }
            else if (!empty($subTotalColVal) &&
                     strpos($subTotalColVal, $adHocText) !== false)
            {
                $totalCost = round($tempRow[$totalCol], 2);
                
                // This is an ad hoc charging row so output with special
                // formatting
                $tempRow[$totalCol] = "£" . number_format($totalCost, 2);
                
                $runningSubTotal += $totalCost;
                $runningGrandTotal += $totalCost;
            }
            else if (array_key_exists($audioId, $billingData))
            {
                $rate = $billingData[$audioId]["_rate"];
                $audioDiscount = $billingData[$audioId]["_audio_job_discount"];
                $jobDiscount = $billingData[$audioId]["_job_discount"];
                $clientDiscount = $billingData[$audioId]["_client_discount"];
                $poorAudio = $billingData[$audioId]["_poor_audio"];
                $seconds = $billingData[$audioId]["_length_seconds"];
                $additionalServices = $billingData[$audioId]["_total_additional_services_price"];
                $quarters = null;
                $calculator = null;
                $price = null;
                $totalCost = null;
                
                // Create and set-up the price calculator
                $calculator = new App_AudioJob_PriceCalculator();
                $calculator->setRate($rate);
                $calculator->setLengthSeconds($seconds);
                $calculator->setAdditionalServicesPrice($additionalServices);
                $calculator->setAudioJobDiscount($audioDiscount);
                
                // Calculate the price for this audio job
                $price = $calculator->calculatePrice();
                $quarters = $calculator->getQuarterHours();
                
                // Adjust for poor audio quality (note that decimals aren't
                // empty when 0)
                if (!empty($poorAudio) && $poorAudio > 0)
                {
                    $price *= $poorAudio;
                }
                
                // Work out the overall project discount (note that decimals
                // aren't empty when 0)
                if (!empty($jobDiscount) && $jobDiscount > 0)
                {
                    $tempRow["project_discount"] = $jobDiscount;
                }
                else if (!empty($clientDiscount) && $clientDiscount > 0)
                {
                    $tempRow["project_discount"] = $clientDiscount;
                }
                else
                {
                    $tempRow["project_discount"] = 0;
                }
                
                // Calculate the total price for the job minus discount
                if (empty($tempRow["project_discount"]))
                {
                    $totalCost = $price;
                }
                else
                {
                    $totalCost = $price -
                             (($price / 100) * $tempRow["project_discount"]);
                }
                $totalCost = round($totalCost, 2);
                
                // Populate and format billing data
                $tempRow["for_billing"] = $quarters / 4;
                $tempRow["billing_rate"] = $rate * 4;
                $tempRow["per_min_cost"] = round($rate / 15, 14);
                $tempRow["sub_total"] = "£" . number_format($price, 2);
                $tempRow[$totalCol] = "£" . number_format($totalCost, 2);
                $tempRow["project_discount"] .= "%";
                
                // Add to sub total
                $runningSubTotal += $totalCost;
                $runningGrandTotal += $totalCost;
            }
            
            $returnData[] = array_values($tempRow);
        }
        
        return $returnData;
    }

    /**
     * Convert an array of strings into an associative array with those strings
     * as headings
     *
     * @param array $source
     *            array of strings to convert into associative array keys
     *            
     * @return array with strings as keys and null values
     */
    protected function flip_array_to_empty ($source)
    {
        $flipped = null;
        $count = count($source);
        
        $flipped = array_flip($source);
        
        foreach ($flipped as $key => $val)
        {
            $flipped[$key] = null;
        }
        
        return $flipped;
    }

    /**
     * Return a comma separated list of service group names.
     * Only called for
     * clients with more than one
     *
     * @param int $client_id
     *            The ID of the client to retrieve services for
     *            
     * @return string comma separated list of names
     */
    protected function get_multi_client_service_groups ($client_id)
    {
        $db = $this->getAdaptor();
        $stmt = null;
        $names = array();
        $args = array($client_id);
        
        $sql = "SELECT ssg.name name
				FROM s_client_service_group AS scsg
				LEFT OUTER JOIN s_service_group AS ssg ON ssg.id = scsg.service_group_id
				WHERE scsg.client_id = ?";
        
        $stmt = new Zend_Db_Statement_Mysqli($db, $sql);
        $stmt->execute($args);
        
        // Loop through the rows to get the names
        while ($row = $stmt->fetch())
        {
            $names[] = $row["name"];
        }
        
        return implode(", ", $names);
    }
    
    /**
     * Return a comma separated list of additional service names.
     * 
     * Only called for audio jobs with multiple price modifiers
     *
     * @param int $audio_job_id
     *            The ID of the audio job with multiple service modifiers
     *
     * @return string comma separated list of names
     */
    protected function get_multi_price_modifier_names ($audio_job_id)
    {
    	$db = $this->getAdaptor();
    	$stmt = null;
    	$names = array();
    	$args = array($audio_job_id);
    
    	$sql = "SELECT spm.name
                FROM audio_jobs_price_modifiers ajpm
                INNER JOIN s_service_price_modifier AS sspm ON sspm.id = ajpm.service_price_modifier_id
                INNER JOIN s_price_modifier AS spm ON spm.id = sspm.price_modifier_id
                WHERE audio_job_id = ?";
    
    	$stmt = new Zend_Db_Statement_Mysqli($db, $sql);
    	$stmt->execute($args);
    
    	// Loop through the rows to get the names
    	while ($row = $stmt->fetch())
    	{
    		$names[] = $row["name"];
    	}
    
    	return implode(", ", $names);
    }    
}

