<?php
/* Finds all dates and date ranges which should be displayed
 * in the Special Dates section; specifically, generates ranges
 * based on which dates are marked as exceptions and combining
 * dates of exceptional closures with adjacent non-exceptional
 * closures (e.g. - to identify 3-day weekends if a Monday is a
 * holiday), including ranges which began up to a month before
 * the current date but which extend to today or beyond. The
 * elements in the resultant associative array take the form:
 * "startDate" => array( "endDate" , "reason" )
 * Ranges of size 1 will simply have the same "startDate" and
 * "endDate"; it is assumed that the caller will differentiate
 * between legitimate ranges and singletons.
 */
function getSpecialDates(){
	$today = date('Y-m-d',strtotime('0 months'));//now
	$buffer = '-1 month';
	$term = '+3 months';
	$start = date('Y-m-d',strtotime($buffer,strtotime($today))); //echo $start;
	$end = date('Y-m-d',strtotime($term,strtotime($today))); //echo $end;

	$exceptions = getOpenExceptions($start, $end);		// Sort by date all non-closure exceptions
	$closures = getAllClosures($start, $end); 			// Sort by date all closures
	//echo "<br />Closures: ".sizeof($closures);
	
	$output = array();
	
	//Non-closure Ranges
	$range_start = '';
	$range_reason = '';
	$prev = '';
	foreach($exceptions as $date=>$details){
		$curr_reason = $details; 
		//$prev must be set in blocks where these are used
		$prev_reason = $closures[$prev]; 
		$adjacent = date('Y-m-d',strtotime('+1 days',strtotime($prev)));

		if($range_start === ''){						// If no range is currently under consideration
			$range_start = $date;						// start a new range at this date,
			if(
				$curr_reason !== ''						// if this date has a specified reason
				&& !is_null($curr_reason)
			){
				$range_reason = $curr_reason;			// save the reason to this range,
			}
			$prev = $date;								// and ignore prior dates
		}
		else if(										// If this date is adjacent to the immediately
			!strcmp($date,$adjacent)					// previous closure included in this range,
			&& ($prev_reason === $curr_reason			// and their reasons match,
			|| $prev_reason === ''	 					// or one of the two had no provided reason,
			|| $curr_reason === ''						// (which could be indicated either by an
			|| is_null($prev_reason)					//  empty string or by a null value)
			|| is_null($curr_reason))
		){	
			$prev = $date;								// then add this date to the current range,                                                   
			if(
				$range_reason === '' 					// if the range doesn't currently have
				|| is_null($range_reason)				// a reason assigned (empty string or null)
			){					
				$range_reason = $curr_reason;			// save the reason associated with this range,
			}
		}
		else											// If a range is specified, but this date is not 
		{												// consecutive or its reason doesn't match
			$range_info = array($prev, $range_reason);	// save the reason and end date for the range,
			if($prev >= $today){						// if this range overlaps with the current term
				$output[$range_start] = $range_info;	// then save it to be output to Special Dates,
			}
			$range_start = $date;						// and this date is the start of a new range:

			if(
				$curr_reason !== ''						// if this date has a specified reason
				|| !is_null($curr_reason)
			){					
				$range_reason = $curr_reason;			// save the reason to this range,
			}
			$prev = $date;								// and ignore prior dates
		}
	}
	if($prev >= $today){				// Close up the last opened range
		$output[$range_start] = 
			array($prev,$range_reason);
	}


	//Closure Ranges
	$range_start = '';
	$range_reason = '';
	$range_x = 0;
	$prev = '';
	foreach($closures as $date=>$details){
		$curr_reason = $details[1]; 
		//$prev must be set in blocks where these are used
		$prev_reason = $closures[$prev][1]; 
		$adjacent = date('Y-m-d',strtotime('+1 days',strtotime($prev)));

		if($range_start === ''){						// If no range is currently under consideration
			$range_start = $date;						// start a new range at this date,
			if($details[0] == '1') $range_x = 1;		// if this date is special so is the range,
			if(
				$curr_reason !== ''						// if this date has a specified reason
				&& !is_null($curr_reason)
			){
				$range_reason = $curr_reason;			// save the reason to this range,
			}
			$prev = $date;								// and ignore prior dates
		}
		else if(										// If this date is adjacent to the immediately
			!strcmp($date,$adjacent)					// previous closure included in this range,
			&& ($prev_reason === $curr_reason			// and their reasons match,
			|| $prev_reason === ''	 					// or one of the two had no provided reason,
			|| $curr_reason === ''						// (which could be indicated either by an
			|| is_null($prev_reason)					//  empty string or by a null value)
			|| is_null($curr_reason))
		){	
			$prev = $date;								// then add this date to the current range,                                                   
			if(
				$range_reason === '' 					// if the range doesn't currently have
				|| is_null($range_reason)				// a reason assigned (empty string or null)
			){					
				$range_reason = $details[1];			// save the reason associated with this range,
			}
			if($details[0] == '1') $range_x = 1;		// and, if this date is special, so is the range.
		}
		else											// If a range is specified, but this date is not 
		{												// consecutive or its reason doesn't match
			$reason = "Closed: ".$range_reason;			// then format the closure reason
			if(!strcmp($range_reason,''))				// (Should this be the caller's responsibility?),
				$reason = "Closed.";
			$range_info = array($prev, $reason);		// save the reason and end date for the range,
			if($prev >= $today && $range_x == 1){		// if this range overlaps with the current term
				$output[$range_start] = $range_info;	// then save it to be output to Special Dates,
			}
			$range_start = $date;						// and this date is the start of a new range:
			if($details[0] == '1') $range_x = 1;		// if this date is special so is the range,
			if(
				$curr_reason !== ''						// if this date has a specified reason
				|| !is_null($curr_reason)
			){					
				$range_reason = $curr_reason;			// save the reason to this range,
			}
			$prev = $date;								// and ignore prior dates
		}
	}
	if($prev >= $today && $range_x == 1){				// Close up the last opened range
		if($range_reason === '' || is_null($range_reason))
			$reason = "Closed.";
		else $reason = "Closed: $range_reason";
		$output[$range_start] = array($prev,$reason);
	}


	//print("<pre>");print_r($output);print("</pre>");
	ksort($output);
	return $output;
}

/* A helper function responsible for querying the database and
 * returning only dates for which the exception flag is raised,
 * but the closure flag is NOT, the department is 'library' and
 * date is within the specified range.
 * Elements in the returned array are of the following format:
 * "date" => "reason"
 */
function getOpenExceptions($start, $end){
	$select = "select * from psu_hours where date >= '$start' and exception like '1' and 
		closed like '0' and dept = 'library' and active like '1' order by date";	
	$result = mysql_query($select);
	$exceptions = array();
	while($day = mysql_fetch_object($result)){
		$exceptions[$day->date] = $day->exception_reason;
	}
	//echo "<pre>"; print_r($exceptions); echo "</pre>";
	//echo "<br />Exceptions: ".sizeof($exceptions);
	return $exceptions;
}

/* A helper function responsible for querying the database and
 * returning only dates for which the closure flag is raised,
 * the department is 'library', & in the specified date range.
 * Elements in the returned array are of the following format:
 * "date" => array( exception_value , "reason" )
 */
function getAllClosures($start, $end, $conn_localhost){
	$select = "select * from psu_hours where date >= '$start' and exception like '1' and 
		closed like '1' and dept = 'library' and active like '1' order by date";
	$result = mysql_query($select);
	$closures = array();
	while($day = mysql_fetch_object($result)){
		$closures[$day->date] = array($day->exception,$day->exception_reason);
	}
	//echo "<pre>"; print_r($closures); echo "</pre>";
	//echo "<br />Closures: ".sizeof($closures);
	return $closures;
}

?>