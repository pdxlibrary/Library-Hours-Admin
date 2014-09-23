<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With,EXLRequestType');
header('X-XSS-Protection: 0');
header("content-type: application/json");

require_once("../includes/general_site_info.php");
require_once("../includes/cluster_connect.php");
require_once("includes/xml_functions.php");

if(isset($_GET['start_date']) && strtotime($_GET['start_date']) > 0 && isset($_GET['end_date']) && strtotime($_GET['end_date']) > 0)
{
	$start_date = date('Y-m-d',strtotime($_GET['start_date']));
	$end_date = date('Y-m-d',strtotime($_GET['end_date']));
}
else if(isset($_GET['date']) && strtotime($_GET['date']) > 0)
{
	$start_date = date('Y-m-d',strtotime($_GET['date']));
	$end_date = date('Y-m-d',strtotime($_GET['date']));
}
else
{
	$start_date = date('Y-m-d',strtotime('now'));
	$end_date = date('Y-m-d',strtotime('now'));
}

switch($_GET['dept'])
{
	// allowed depts
	case 'reference_desk':
		$dept = $_GET['dept'];
		break;
	default:
		$dept = "library";
}

$hours_by_day = array();
// load default labels
$counter = 0;
for($i = strtotime($start_date); $i <= strtotime($end_date); $i = strtotime("+1 day",$i))
{
	$index = date("Y-m-d",$i);
	unset($hours);
	$hours->date = $index;
	$hours->hours_label = "To be decided";
	$hours_by_day[$index] = $hours;
	$counter++;
	if($counter > 365)
		break;
}

$possible_24_hours = false;
$select = "select * from psu_hours where dept like '$dept' and date >= '$start_date' and date <= '$end_date' and active like '1' order by date limit 365";
if(isset($_GET['debug'])) print("select: $select<br>\n");

$res = mysql_query($select,$conn_localhost);
while($day = mysql_fetch_object($res))
{
	if(isset($_GET['debug'])) print_r($day);
	if(!strcmp($day->closed,'1'))
		$day->hours_label = "Library Closed";
	else
	{
		$day->open_time = date('Y-m-d H:i:s',strtotime($day->date . " " . $day->open_hour));
		$day->close_time = date('Y-m-d H:i:s',strtotime($day->date . " " . $day->close_hour));
		$day->open_hour = date("g:ia",strtotime($day->open_time));
		$day->close_hour = date("g:ia",strtotime($day->close_time));
		if(!strcmp($day->open_hour,'12:00am'))
		{
			$day->open_hour = "Midnight";
			$possible_24_hours = true;
		}
		if(!strcmp($day->close_hour,'12:00am'))
		{
			$day->close_hour = "Midnight";
			$possible_24_hours = true;
		}
		if(!strcmp($day->open_hour,"Midnight") && !strcmp($day->close_hour,"Midnight"))
		{
			$day->hours_label = "Open 24 Hours";
			$possible_24_hours = true;
		}
		else
			$day->hours_label = $day->open_hour." - ".$day->close_hour;
			
		if($possible_24_hours)
		{
			// special 24 hours cases:
				// N: not midnight, M: Midnight, C: Closed ?: Any
				
				// yesterday: ?-C	today: M-M		tomorrow: M-?	--> start of 24 hours block today
				// yesterday: ?-N	today: M-M		tomorrow: M-?	--> start of 24 hours block today
				// yesterday: ?-?	today: N-M		tomorrow: M-?	--> start of 24 hours block today
				
				// yesterday: ?-M	today: M-M		tomorrow: C-?	--> end of 24 hours block today
				// yesterday: ?-M	today: M-N		tomorrow: ?-?	--> end of 24 hours block today
				// yesterday: ?-M	today: M-M		tomorrow: N-?	--> end of 24 hours block today
			
			// load yesterday
			$yesterday = date("Y-m-d",strtotime("yesterday",strtotime($day->date)));
			$select = "select * from psu_hours where dept like '$dept' and date = '$yesterday' and active like '1' limit 1";
			if(isset($_GET['debug'])) print("select: $select<br>\n");
			$yesterday_res = mysql_query($select,$conn_localhost);
			while($prev_day = mysql_fetch_object($yesterday_res))
			{
				if(!strcmp($prev_day->closed,'1'))
				{
					$prev_day->open_time = "";
					$prev_day->close_time = "";
					$prev_day->open_hour = "";
					$prev_day->close_hour = "";
				}
				else
				{
					$prev_day->open_time = date('Y-m-d H:i:s',strtotime($prev_day->date . " " . $prev_day->open_hour));
					$prev_day->close_time = date('Y-m-d H:i:s',strtotime($prev_day->date . " " . $prev_day->close_hour));
					$prev_day->open_hour = date("g:ia",strtotime($prev_day->open_time));
					$prev_day->close_hour = date("g:ia",strtotime($prev_day->close_time));
				}
				
				if(isset($_GET['debug']))
				{
					print("previous day<br>\n");
					print_r($prev_day);
				}
				break;
			}
			
			// load tomorrow
			$tomorrow = date("Y-m-d",strtotime("tomorrow",strtotime($day->date)));
			$select = "select * from psu_hours where dept like '$dept' and date = '$tomorrow' and active like '1' limit 1";
			if(isset($_GET['debug'])) print("select: $select<br>\n");
			$tomorrow_res = mysql_query($select,$conn_localhost);
			while($next_day = mysql_fetch_object($tomorrow_res))
			{
				if(!strcmp($next_day->closed,'1'))
				{
					$next_day->open_time = "";
					$next_day->close_time = "";
					$next_day->open_hour = "";
					$next_day->close_hour = "";
				}
				else
				{
					$next_day->open_time = date('Y-m-d H:i:s',strtotime($next_day->date . " " . $next_day->open_hour));
					$next_day->close_time = date('Y-m-d H:i:s',strtotime($next_day->date . " " . $next_day->close_hour));
					$next_day->open_hour = date("g:ia",strtotime($next_day->open_time));
					$next_day->close_hour = date("g:ia",strtotime($next_day->close_time));
				}
				
				if(isset($_GET['debug']))
				{
					print("next day<br>\n");
					print_r($next_day);
				}
				break;
			}
			
			// yesterday: ?-C	today: M-M		tomorrow: M-?	--> start of 24 hours block today
			if(!strcmp($prev_day->closed,'1') && !strcmp($day->open_hour,'Midnight') && !strcmp($day->close_hour,'Midnight') && !strcmp($next_day->open_hour,'12:00am'))
				$day->hours_label = "Start 24/7 (open at $day->open_hour)";
				
			// yesterday: ?-N	today: M-M		tomorrow: M-?	--> start of 24 hours block today
			else if(strcmp($prev_day->close_hour,'12:00am') && !strcmp($day->open_hour,'Midnight') && !strcmp($day->close_hour,'Midnight') && !strcmp($next_day->open_hour,'12:00am'))
				$day->hours_label = "Start 24/7 (open at $day->open_hour)";
		
			// yesterday: ?-?	today: N-M		tomorrow: M-?	--> start of 24 hours block today
			else if(strcmp($day->open_hour,'Midnight') && !strcmp($day->close_hour,'Midnight') && !strcmp($next_day->open_hour,'12:00am'))
				$day->hours_label = "Start 24/7 (open at $day->open_hour)";
			
			// yesterday: ?-M	today: M-M		tomorrow: C-?	--> end of 24 hours block today
			else if(!strcmp($prev_day->close_hour,'12:00am') && !strcmp($day->open_hour,'Midnight') && strcmp($day->close_hour,'Midnight') && !strcmp($next_day->closed,'1'))
				$day->hours_label = "Closing at $day->close_hour";
				
			// yesterday: ?-M	today: M-N		tomorrow: ?-?	--> end of 24 hours block today
			else if(!strcmp($prev_day->close_hour,'12:00am') && !strcmp($day->open_hour,'Midnight') && strcmp($day->close_hour,'Midnight'))
				$day->hours_label = "Closing at $day->close_hour";
			
			// yesterday: ?-M	today: M-M		tomorrow: N-?	--> end of 24 hours block today
			else if(!strcmp($prev_day->close_hour,'12:00am') && !strcmp($day->open_hour,'Midnight') && !strcmp($day->close_hour,'Midnight') && strcmp($next_day->open_hour,'12:00am'))
				$day->hours_label = "Closing at $day->close_hour";
		}
	}
	$hours_by_day[$day->date] = $day;
}

if(isset($_GET['debug']))
{
	print("data to be sent in json object:\n");
	print_r($hours_by_day);
}

print(json_encode($hours_by_day));

exit();


?>