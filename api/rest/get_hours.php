<?php

require_once("../includes/general_site_info.php");
require_once("../includes/cluster_connect.php");
require_once("includes/xml_functions.php");

// TODO: Input validation and error reporting
if(isset($_GET['dept']))
{
	switch($_GET['dept'])
	{
		case 'reference_desk':	$dept = "reference_desk"; break;
		default: $dept = "library";
	}
}
else
	$dept = "library";

if(isset($_GET['start_date']) && isset($_GET['end_date']))
{
	$start_date = $_GET['start_date'];
	$end_date = date('Y-m-d',strtotime("+1 day",strtotime($_GET['end_date'])));
}
else if(isset($_GET['num_days']))
{
	$num_days = $_GET['num_days'];
	$start_date = date('Y-m-d',strtotime('now'));
	$end_date = date('Y-m-d',strtotime('+'.$num_days.' days'));
}
else
{
	$m = (!$_GET['m']) ? date("n",mktime()) : $_GET['m'];
	$y = (!$_GET['y']) ? date("Y",mktime()) : $_GET['y'];
		
	$start_date = date('Y-m-d',strtotime("$m/1/$y"));
	$end_date = date('Y-m-d',strtotime("+1 month",strtotime("$m/1/$y")));
}


$hours_by_day = array();

$select = "select * from psu_hours where dept like '$dept' and date >= '$start_date' and date < '$end_date' and active like '1' order by date";
//print("select: $select<br>\n");
$res = mysql_query($select,$conn_localhost);
while($day = mysql_fetch_object($res))
{
	$i = date('Ymd',strtotime($day->date));
	$day->open_time = date('Y-m-d H:i:s',strtotime($day->date . " " . $day->open_hour));
	$day->close_time = date('Y-m-d H:i:s',strtotime($day->date . " " . $day->close_hour));
	$hours_by_day[$i] = $day;
	//print_r($day);
}

if(isset($_GET['debug']))
{
	print("<pre>\n");
	print_r($hours_by_day);
	print("</pre>\n");
}
else
	header("content-type: text/xml");
	
$xml = new SimpleXMLElement("<?xml version=\"1.0\" standalone=\"yes\"?><Result></Result>");
$days = $xml->addChild('Days');

/*
                    [date] => 2011-02-26
                    [open_hour] => 10:00am
                    [close_hour] => 7:00pm
                    [closed] => 0
                    [closure_reason] => 
                    [entry_date] => 2010-11-01 09:35:00
                    [active] => 1
*/

$fields = array();
$fields["date"] = "Date";
$fields["open_time"] = "OpenTime";
$fields["close_time"] = "CloseTime";
$fields["closed"] = "Closed";
$fields["closure_reason"] = "ClosureReason";

foreach($hours_by_day as $day_obj)
{
	$day = $days->addChild('Day');
	
	foreach($fields as $db_field => $xml_field)
		$day->addChild($xml_field,encodeXML($day_obj->$db_field));
}

print($xml->asXML());
exit();


?>