<?php

$xml_result = file_get_contents("http://library.pdx.edu/api/rest/hours/hours_open.php?num_days=4");
$xml = new SimpleXMLElement($xml_result);


$fields = array();
$fields["date"] = "Date";
$fields["open_hour"] = "OpenHour";
$fields["close_hour"] = "CloseHour";
$fields["closed"] = "Closed";
$fields["closure_reason"] = "ClosureReason";

$all_hours = array();


foreach ($xml->Days->Day as $day)
{
	foreach($fields as $db_field => $xml_field)
		$day_obj->$db_field = ((string)$day->$xml_field);
	
	$all_hours[] = $day_obj;
	unset($day_obj);
}

print("<pre>\n");
print_r($all_hours);
print("</pre>\n");

exit();

?>