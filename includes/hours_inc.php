<?php
/***********************************************************************************
* hours_inc.php
*
* Gets hours of library and closures.  Envoked by hours.php
* 
***********************************************************************************/

// get date and time
$now = strtotime("now");
$today = date('Y-m-d',$now);
$tomorrow = date('Y-m-d',strtotime('+1 day',$now));

// get date one week from now
$one_week = date('Y-m-d',strtotime('+1 week',$now));

// get this month
$this_month = strtoupper(date('M',$now));
$this_date = date('d',$now);

// Gets hours for the next week
$select_hours = "select * from psu_hours where date >= '$today' and date < '$one_week' and active like '1' order by date";
$res_hours = mysql_query($select_hours);

$current_week = array();
while($day = mysql_fetch_object($res_hours)){

	$day_title = date('D',strtotime($day->date));
	if($day->date == $today) $day_title = "Today";
	if($day->date == $tomorrow) $day_title = "Tomorrow";
	if(!strcmp($day->closed,'1'))
		$current_week[$day_title] = "Closed";
	else
		$current_week[$day_title] = str_replace(" ","",$day->open_hour)."<br />-<br />".str_replace(" ","",$day->close_hour);
}

//Generate array of hours for the next week for the given dept
function getHours($dept){
	// get date and time
	$now = strtotime("now");//$now = strtotime("-4 months");
	$today = date('Y-m-d',$now);
	$tomorrow = date('Y-m-d',strtotime('+1 day',$now));
	// get date one week from now
	$one_week = date('Y-m-d',strtotime('+1 week',$now));

	// select this week's hours
	$select_hours = "select * from psu_hours where date >= '$today' and date < '$one_week' and dept = '$dept' and active like '1' order by date";
	$res_hours = mysql_query($select_hours);
	$current_week = array();
	while($day = mysql_fetch_object($res_hours))
	{
		$day->open_hour = str_replace(" ","",$day->open_hour);
		$day->close_hour = str_replace(" ","",$day->close_hour);
		
		if(!strcmp($day->open_hour,'12:00am')) $day->open_hour = "Midnight";
		if(!strcmp($day->close_hour,'12:00am')) $day->close_hour = "Midnight";
		
		$day_title = date('D',strtotime($day->date));
		if($day->date == $today) $day_title = "Today";
		if($day->date == $tomorrow) $day_title = "Tomorrow";
		if(!strcmp($day->closed,'1'))
			$current_week[$day_title] = "Closed";
		else if(!strcmp($day->open_hour,'Midnight') && !strcmp($day->close_hour,'Midnight'))
			$current_week[$day_title] = "Open<br />24<br />Hours";
		else
			$current_week[$day_title] = $day->open_hour."<br />-<br />".$day->close_hour;
		//echo $day_title.": ".$current_week[$day_title]."<hr />";
	}
	return $current_week;
}

//Generates the HTML to display this week's hours for a given department
function displayHours($col,$title,$text){
	$current_week = getHours($col);
	print(	'<div class="left_cont">
				<h2 style="float: left;">'.$title.'</h2>'
				.$text.
				'<table class="left_cont">
					<thead>
						<tr class="days">'	);
	//Print headings
	foreach($current_week as $day=>$hours){
		if(!strcmp($day, "Today")) 
			print("<th class='today'>$day</th>"); 
		else if(!strcmp($day, "Tomorrow")) 
			print("<th class='tomorrow'>$day</th>");
		else print("<th>$day</th>"); 
	}
	print(				'</tr>
					</thead>
					<tbody>
						<tr class="days">	');
	//Print hours
	foreach($current_week as $day=>$hours){
		if(!strcmp($hours, "Closed")) 
			print("<td class='closed'>$hours</td>");
		else 
			print("<td>$hours</td>"); 
	}
	print(	'			</tr>
					</tbody>
				</table>
			</div>				');
}

//Takes a date in YYYY-mm-dd form and returns its hours
function getDay($date, $conn_localhost)
{
	$select = "select * from psu_hours where date = '$date' and dept='library' and active like '1' order by date";
	//echo $select;
	$result = mysql_query($select);
	if(mysql_num_rows($result) == 0) return false;
	
	$results = array();
	while($day = mysql_fetch_object($result))
	{
		$day->open_hour = str_replace(" ","",$day->open_hour);
		$day->close_hour = str_replace(" ","",$day->close_hour);
		
		if(!strcmp($day->open_hour,'12:00am')) $day->open_hour = "Midnight";
		if(!strcmp($day->close_hour,'12:00am')) $day->close_hour = "Midnight";
			
		if(!strcmp($day->closed,'1') && strcmp(trim($day->exception_reason),''))
			$results[] = "Closed: ".trim($day->exception_reason);
		else if(!strcmp($day->closed,'1'))
			$results[] = "Closed.";
		else if(!strcmp($day->open_hour,'Midnight') && !strcmp($day->close_hour,'Midnight'))
			$results[] = "Open 24 Hours";
		else
			$results[] = array('open' => $day->open_hour, 'closed' => $day->close_hour);
	}
	return $results;
}


// Determines if 2 days are one-after-the-other (Sequential)
function isSequential($date1,$date2)
{
	$tomorrow = date('Y-m-d',strtotime("tomorrow",strtotime($date1)));
	return(!strcmp($tomorrow,$date2));
}

// This function will shift array elements every time it is called, the first element rolls off and the array is "eaten"
function array_kshift(&$arr)
{
	list($k) = array_keys($arr);//saves the first key
	$r  = array($k=>$arr[$k]);//create an array of firstkey=>firstvalue
	unset($arr[$k]);//empty the original array
	return $r;//return the truncated version
}
//Displays hour irregularities for the "Special Dates" section
/* 
 * where all closures are equal but closures only listed as a special  date
 * if at least one in a string of closures is also labeled as an exception
*/
function specialDates($conn_localhost)
{
	// Get today's time and date
	$today = date('Y-m-d',strtotime("now"));	//$today =  date('Y-m-d',strtotime('-10 day',strtotime($today)));//for debugging
	
	//Buffer date, to check before today for any closures that begin previously and extend until now or later
	$buffer = '-1 month';
	$start_date = date('Y-m-d',strtotime($buffer,strtotime($today)));	
	
	// Get date 3 months from today (roughly 1 term - decided at OUI meeting)
	$end_date = date('Y-m-d',strtotime('+3 month',strtotime($today)));
	$end_buffer_date = date('Y-m-d',strtotime('+1 month',strtotime($end_date)));

	//Get irregular hours for the next 3 months (roughly one term - decided at OUI)
	$select_specialDates = "select * from psu_hours where date >= '$start_date' and date < '$end_buffer_date' and exception like '1' and closed like '0' and dept = 'library' and active like '1' order by date";	
	$specialDates_result = mysql_query($select_specialDates, $conn_localhost);
	$specialDate_count = mysql_num_rows($specialDates_result);
	
	//Get closed dates for the next 3 months (to combine exceptional closures with weekends/regular closures) (+1 month if a range goes past the 3 month mark)
	$select_closures = "select * from psu_hours where date >='$start_date' and date < '$end_buffer_date' and closed like '1' and dept = 'library' and active like '1' order by date";
	$closure_result = mysql_query($select_closures, $conn_localhost);
	
	$exceptions = array();
	$closures = array();
	
	//save the rows as objects to arrays for easier processing.
	while($day = mysql_fetch_object($specialDates_result)){
		$exceptions[$day->date] = $day;
	}
	while($day = mysql_fetch_object($closure_result)){
		$closures[$day->date] = $day;
	}
	
	//generate irregular ranges
	reset($exceptions);
	$exception_ranges = array();
	while(current($exceptions) !== false){
		$start = current($exceptions);
		$end = $start;
		$reason = $start->exception_reason;
		//check the following array elements for consecutive dates
		while(date('Y-m-d',strtotime('+1 day',strtotime($end->date))) == next($exceptions)->date
		&& (current($exceptions)->exception_reason == null || !strcmp($reason, current($exceptions)->exception_reason)))
		{
			$end = current($exceptions);
		}
		//only register exceptions within a 3 month period
		if($end->date >= $today && $start->date <= $end_date){
			$exception_ranges[] = array($start, $end, $reason);
			//echo $start->date." - ".$end->date.": |".$reason."|<br /><br />";//for debugging
		}
	}
	
	//generate closure ranges
	reset($closures);
	$closure_ranges = array();
	while(current($closures) !== false){
		$start = current($closures); //echo $start->date." <- start.<br />";
		$end = $start;	
		$x = $end->exception;//start counting days in this range with exceptions (days without accumulate 0)
		$reason = $start->exception_reason;
		while(date('Y-m-d',strtotime('+1 day',strtotime($end->date))) == next($closures)->date
		&& (current($exceptions)->exception_reason == null || !strcmp($reason, current($exceptions)->exception_reason)))
		{
			$end = current($closures);
			$x += $end->exception;
		}
		//only register exceptional closures (to avoid displaying weekends as special) within a 3 month period
		if($x > 0 && $end->date >= $today && $start->date <= $end_date){
			$closure_ranges[] = array($start, $end, $reason);
			//echo $x.": ".$start->date." - ".$end->date.": |".$reason."|<br /><br />";//for debugging
		}
		$x = 0;
		$reason = "";
	}
	
	//output values in order
	reset($exception_ranges);
	reset($closure_ranges);
	if(count($exception_ranges)+count($closure_ranges) == 0)
		echo "<li><span>No special dates for the next 3 months.</span></li>\n";
	while(current($exception_ranges) !== false || current($closure_ranges) !== false){
		$e = current($exception_ranges); 
		$c = current($closure_ranges);
		
		echo "<li><span>";
		//both arrays have traversal potential
		if($e !== false && $c !== false){
			if($e[0]->date <= $c[0]->date){
				echo item_out($e);
				next($exception_ranges);
			}
			else{
				echo item_out($c, "Closed:");
				next($closure_ranges);
			}
		}
		else if($e !== false && $c === false){
			echo item_out($e);
			next($exception_ranges);
		}
		else{
			echo item_out($c, "Closed:");
			next($closure_ranges);
		}
			echo "</li>";
	}	
}
//formats the date of a row from psu_hours for display in the special dates area
function item_out($r,$s=""){
	if(is_array($r) && count($r) >= 3 && $r[0]->date != $r[1]->date){
		return $s." ".date('n/j',strtotime($r[0]->date))." - ".date('n/j',strtotime($r[1]->date))."</span>$r[2]";
	}
	else if(is_array($r) && count($r) >= 3 && $r[0]->date == $r[1]->date){
		return $s." ".date('n/j',strtotime($r[0]->date))."</span>$r[2]";
	}
}


// displays library closures
function display_exceptions($conn_localhost)
{
	// Get today's time and date
	$now = strtotime("now");
	$today = date('Y-m-d',$now);
	$today =  date('Y-m-d',strtotime('-10 day',strtotime($today)));
	
	// Get date 3 months from today (roughly 1 term - decided at OUI meeting)
	$one_month = date('Y-m-d',strtotime('+3 month',strtotime($today)));
	
	// Get closures for next month
	$select_hours2 = "select * from psu_hours where date >= '$today' and date < 
					'$one_month' and exception like '1' and active like '1' order by date";
	$res_hours2 = mysql_query($select_hours2); // used to gather exceptions for the month ahead
	
	// Get number of special days this month, create array
	$count_special_days = mysql_num_rows($res_hours2);
	$special_dates = array();
	$closed_banner = "";
	
	// If there is at least 1 closure, calculate, and put days off of 1 or more into clusters
	if($count_special_days > 0)
	{
		// put special days into an array with exception reason
		while($day = mysql_fetch_object($res_hours2))
		{
			$one_week = date('Y-m-d',strtotime('+1 week',strtotime('now')));
			$date = date('Y-m-d',strtotime($day->date));
			$special_dates[$date] = $day->exception_reason;
			$special_dates_reason[$date] = $day->exception_reason;  // had to make a separate array because the array_shift below eats up the special_dates array
		}
		//print_r($special_dates);
		$show_exceptions = array_sum($special_dates);
		
		// if only one close date, do not array shift
		if($count_special_days == 1)
		{
			// initialize date
			$date_arr = $special_dates;
			$last_date = key($date_arr);
			$special_dates_string = /*date('D',strtotime($last_date))." ".*/date('M d',strtotime($last_date));
			$date_arr = $special_dates;//what's this for?
			$date = key($date_arr);//what's this for?
		}
		else
		{
			// initialize date - using array shift, we want to display days off as a range, not singular days off listed out
			$date_arr = array_kshift($special_dates);//split the first element of special_dates off and call it date_arr
			$last_date = key($date_arr);//save the first special date
			$special_dates_string = "<em>". /*date('D',strtotime($last_date)).*/" ".date('m/d',strtotime($last_date)) ."</em>";
			$date_arr = array_kshift($special_dates); //what's this for?
			$date = key($date_arr); //what's this for?
			//print("HERE: $special_dates_string");
		}
		
		//  display exception reason 
		foreach($special_dates as $exception_date=>$exception_reason)
		{
			if(strcmp($special_dates_reason[$last_date],''))
				$special_dates_text = " <br />Closed: ".$special_dates_reason[$last_date];
			else
				$special_dates_text = ""; // print no reason if not set
				
			// if the date is sequential, then display as first and last date of closure
			if(isSequential($last_date,$date))
			{
			
				while(isSequential($last_date,$date))
				{				
					$last_date = $date;
					$date_arr = array_kshift($special_dates);
					$date = key($date_arr);
					$count++;
					if($count>40) break;
				}

				// concatonate date closure range
				if(strcmp($special_dates_string,date('D',strtotime($last_date))." ".date('m/d',strtotime($last_date))))
					$special_dates_string .= " - <em>". /*date('D',strtotime($last_date)).*/" ".date('m/d',strtotime($last_date)) ."</em>";

				// Show closed date range and closure reason
				print("<li><span>$special_dates_string $special_dates_text</span></li>\n");				
			}
			// display single (orphan) closure dates
			else
			{
				// supress what I can't figure out - aka, work-around - don't judge...
				// for some reason it shows unix epoch date
				if(date('m/d/Y',strtotime($last_date)) != "12/31/1969")
				{
					print("<li><span>$special_dates_string $special_dates_text</span></li>\n");
				}
			}
			
			// move to next date (linear linked list style)
			$last_date = key($date_arr);
			$special_dates_string = "<em>". /*date('l',strtotime($last_date)).*/" ".date('m/d',strtotime($last_date)) ."</em>";
			$date_arr = array_kshift($special_dates);
			$date = key($date_arr);	
		}
		
	}
	else
	{
		print("<li><span>No closures for the next month.</span></li>\n");		
	}
}

function getTerm($conn_localhost){
	$today = date('Y-m-d',strtotime("now"));
	$select = "select * from psu_terms where start_date <= '$today' and end_date >= '$today' and active like '1' order by start_date";
	$result = mysql_query($select);
	$term = mysql_fetch_object($result);
	return $term->term_name;
}
?>