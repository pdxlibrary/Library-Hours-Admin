<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With');

header("content-type: application/json");	

require_once("../includes/general_site_info.php");
require_once("../includes/cluster_connect.php");
require_once("includes/xml_functions.php");

$today = date('Y-m-d',strtotime('now'));

$hours_by_day = array();

$select = "select * from psu_hours where dept like 'library' and date = '$today' and active like '1' order by date";
// print("select: $select<br>\n");
$res = mysql_query($select,$conn_localhost);
if(mysql_num_rows($res) == 1)
{
	$day = mysql_fetch_object($res);
	if(!strcmp($day->closed,'1'))
		$day->hours_label = "Library Closed";
	else
	{
		$day->open_time = date('Y-m-d H:i:s',strtotime($day->date . " " . $day->open_hour));
		$day->close_time = date('Y-m-d H:i:s',strtotime($day->date . " " . $day->close_hour));
		$day->open_hour = date("g:ia",strtotime($day->open_time));
		$day->close_hour = date("g:ia",strtotime($day->close_time));
		if(!strcmp($day->open_hour,'12:00am'))
			$day->open_hour = "Midnight";
		if(!strcmp($day->close_hour,'12:00am'))
			$day->close_hour = "Midnight";
		if(!strcmp($day->open_hour,"Midnight") && !strcmp($day->close_hour,"Midnight"))
			$day->hours_label = "Open 24 Hours";
		else
			$day->hours_label = $day->open_hour." - ".$day->close_hour;
	}
	$hours_by_day["today"] = $day;
}
else
{
	// hours not known... just fail silently/gracefully
	$hours_by_day["today"] = "";
}

if(isset($_GET['debug']))
{
	print("data to be sent in json object:\n");
	print_r($hours_by_day);
}

print(json_encode($hours_by_day));

exit();


?>