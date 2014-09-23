<?php

	session_start();
	set_time_limit(60);
	require_once("includes/general_site_info.php");
	require_once("includes/cluster_connect.php");
	require_once("manage/includes/class.phpmailer.php");
	require_once("manage/includes/class.smtp.php");
	require_once("manage/includes/mysql_cluster_functions.php");
	require_once("manage/includes/verify_access.php");


				
	$dept = $_GET['dept'];
	if(!strcmp($dept,'')) $dept = "library"; 
	$dept_name = ucwords(str_replace('_',' ',$dept));
	
	if(strcmp($_GET['start_date'],'') && strcmp($_GET['end_date'],'') && strcmp($dept,''))
	{
		// add hours to the hours database
		$start_date_stamp = strtotime($_GET['start_date']);
		$end_date_stamp = strtotime($_GET['end_date']);
		$current_date_stamp = $start_date_stamp;
				
		if(!strcmp($_GET['special'],'on')) $special = 1;
		else $special = 0;
		
		$reason = $_GET['exception_reason'];
		
		if(strcmp($_GET['mon_hours_start'],'') || strcmp($_GET['tue_hours_start'],'') || strcmp($_GET['wed_hours_start'],'') || strcmp($_GET['thur_hours_start'],'') || strcmp($_GET['fri_hours_start'],'') || strcmp($_GET['sat_hours_start'],'') || strcmp($_GET['sun_hours_start'],''))
		{
			// use ling form for entry
			while($current_date_stamp <= $end_date_stamp)
			{
				$date = date('Y-m-d',$current_date_stamp);
				$dow = date('w',$current_date_stamp);
				
				if($dow == 0)
				{
					// this is a sunday
					if(strcmp($_GET['sun_hours_start'],''))
					{
						$open_hour = $_GET['sun_hours_start'];
						$close_hour = $_GET['sun_hours_end'];
						$closed = 0;
					}
					else
					{
						$closed = '1';
					}
				}
				else if($dow == 1)
				{
					// this is a monday
					if(strcmp($_GET['mon_hours_start'],''))
					{
						$open_hour = $_GET['mon_hours_start'];
						$close_hour = $_GET['mon_hours_end'];
						$closed = 0;
					}
					else
					{
						$closed = '1';
					}
				}
				else if($dow == 2)
				{
					// this is a tuesday
					if(strcmp($_GET['tue_hours_start'],''))
					{
						$open_hour = $_GET['tue_hours_start'];
						$close_hour = $_GET['tue_hours_end'];
						$closed = 0;
					}
					else
					{
						$closed = '1';
					}
				}
				else if($dow == 3)
				{
					// this is a wednesday
					if(strcmp($_GET['wed_hours_start'],''))
					{
						$open_hour = $_GET['wed_hours_start'];
						$close_hour = $_GET['wed_hours_end'];
						$closed = 0;
					}
					else
					{
						$closed = '1';
					}
				}
				else if($dow == 4)
				{
					// this is a thursday
					if(strcmp($_GET['thur_hours_start'],''))
					{
						$open_hour = $_GET['thur_hours_start'];
						$close_hour = $_GET['thur_hours_end'];
						$closed = 0;
					}
					else
					{
						$closed = '1';
					}
				}
				else if($dow == 5)
				{
					// this is a friday
					if(strcmp($_GET['fri_hours_start'],''))
					{
						$open_hour = $_GET['fri_hours_start'];
						$close_hour = $_GET['fri_hours_end'];
						$closed = 0;
					}
					else
					{
						$closed = '1';
					}
				}
				else if($dow == 6)
				{
					// this is a saturday
					if(strcmp($_GET['sat_hours_start'],''))
					{
						$open_hour = $_GET['sat_hours_start'];
						$close_hour = $_GET['sat_hours_end'];
						$closed = 0;
					}
					else
					{
						$closed = '1';
					}
				}
				
				$fields = array('date','dept','open_hour','close_hour','closed','exception_reason','exception');
				$values = array($date,$dept,$open_hour,$close_hour,$closed,$reason,$special);
				//print_r($values);
				$current_date_stamp = strtotime("+1 day",$current_date_stamp);
				update('psu_hours','active','0',"where date like '$date' and dept = '$dept'");
				insert('psu_hours',$fields,$values);
			}
		}
		else if(!strcmp($_GET['closed'],'on'))
		{
			// add closures
			//print("adding a closure<br>\n");
			while($current_date_stamp <= $end_date_stamp)
			{
				$date = date('Y-m-d',$current_date_stamp);
				$fields = array('date','dept','closed','exception_reason','exception');
				$values = array($date,$dept,'1',$reason,$special);
				$current_date_stamp = strtotime("+1 day",$current_date_stamp);
				//print_r($values);
				update('psu_hours','active','0',"where date like '$date' and dept = '$dept'");
				$db_result = insert('psu_hours',$fields,$values);
				if(!$db_result->success) print_errors($db_result);
			}
		}
	}
?>

<link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.8.16.custom.css">
<script src="js/jquery-1.4.2.min.js"></script>
<script src="js/jquery.ui.core.js"></script>
<script src="js/jquery.ui.datepicker.js"></script>
<script src="js/jquery.ui.widget.js"></script>

<script>
$(function() {
		$( "#start_date" ).datepicker();
		$( "#end_date" ).datepicker();
	});
</script>

<style>
.event 		  { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #FFFFFF; text-decoration: none}
.event A:link	  { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #FFFFFF; text-decoration: none}
.event A:visited	  { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #FFFFFF; text-decoration: none}
.event A:hover	  { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; font-weight: bold; color: #FFFFFF; text-decoration: underline}
td {font-size:10pt;}
</style>


<?php
	
	$m = (!$_GET['m']) ? date("n",mktime()) : $_GET['m'];
    $y = (!$_GET['y']) ? date("Y",mktime()) : $_GET['y'];
	
	$start_date = date('Y-m-d',strtotime("$m/1/$y"));
	$end_date = date('Y-m-d',strtotime("+1 month",strtotime("$m/1/$y")));

	$hours_by_day = array();
	
	$select = "select * from psu_hours where date >= '$start_date' and date < '$end_date' and dept = '$dept' and active like '1'";
	//print("select: $select<br>\n");
	$res = mysql_query($select,$conn_localhost);
	while($day = mysql_fetch_object($res))
	{
		$i = date('j',strtotime($day->date));
		$hours_by_day[$i][] = $day;
	}
	//print_r($hours_by_day);


?>


<div id="PSUContent">
<h2>Portland State <?php echo $dept_name; ?> Hours</h2>

<h3><a href='?dept=library'>Manage Library Hours</a></h3>
<h3><a href='?dept=reference_desk'>Manage Reference Desk Hours</a></h3>

<table><tr valign=top><td>

<?php 

	drawCalendar($m,$y,$dept); 

?>

</td><td>

<h2>Set Hours</h2>
<hr>
<form action=''>
<input type=hidden name='dept' value ='<?php print($dept); ?>'>
<input type=hidden name='m' value='<?php print($m); ?>'>
<input type=hidden name='y' value='<?php print($y); ?>'>
<table>
<tr><td>For dates</td><td><input type=text size=10 name=start_date id="start_date"> - <input type=text size=10 name=end_date id="end_date"></td></tr>
<tr><td colspan=2><hr></td></tr>
<tr><td>Monday Hours</td><td><input type=text size=10 name=mon_hours_start> - <input type=text size=10 name=mon_hours_end></td></tr>
<tr><td>Tuesday Hours</td><td><input type=text size=10 name=tue_hours_start> - <input type=text size=10 name=tue_hours_end></td></tr>
<tr><td>Wednesday Hours</td><td><input type=text size=10 name=wed_hours_start> - <input type=text size=10 name=wed_hours_end></td></tr>
<tr><td>Thursday Hours</td><td><input type=text size=10 name=thur_hours_start> - <input type=text size=10 name=thur_hours_end></td></tr>
<tr><td>Friday Hours</td><td><input type=text size=10 name=fri_hours_start> - <input type=text size=10 name=fri_hours_end></td></tr>
<tr><td>Saturday Hours</td><td><input type=text size=10 name=sat_hours_start> - <input type=text size=10 name=sat_hours_end></td></tr>
<tr><td>Sunday Hours</td><td><input type=text size=10 name=sun_hours_start> - <input type=text size=10 name=sun_hours_end></td></tr>
<tr><td colspan=2><small>Note: If an Open time is not given, the day will be marked "Closed".</small><hr></td></tr>
<tr><td colspan=2><input type=checkbox name=closed><?php echo $dept_name;?> is closed for all dates in this range.</td></tr>
<tr><td colspan=2><input type=checkbox name=special>This range of dates should appear in the Special Dates section.<br><br />Reason for closure/Special Dates:<br><input type=text size=50 name=exception_reason></td></tr>
<tr><td colspan=2><hr></td></tr>
</table>
<input type=submit value='Set Hours'>
</form>

</td></tr></table>
 

 <?php

//*********************************************************
// DRAW CALENDAR
//*********************************************************
/*
    Draws out a calendar (in html) of the month/year
    passed to it date passed in format mm-dd-yyyy 
*/
function drawCalendar($m,$y,$dept)
{
	global $hours_by_day;
	
    if (!($m) || !isset($y))
    {
        $m = date("n",mktime());
        $y = date("Y",mktime());
    }

    //print("m/y2: $m/$y<br>\n");
    $today = date("jnY",mktime());

    /*== get what weekday the first is on ==*/
    $tmpd = getdate(mktime(0,0,0,$m,1,$y));
    $month = $tmpd["month"]; 
    $firstwday= $tmpd["wday"];

    $lastday = mk_getLastDayofMonth($m,$y);

?>
<table cellpadding=2 cellspacing=0 border=1>
<tr><td colspan=7 bgcolor="#CCCCDD">
    <table cellpadding=5 cellspacing=0 border=0 width="100%">
<?php
	$prev_month = (($m-1)<1) ? 12 : $m-1;
	$prev_year = (($m-1)<1) ? $y-1 : $y;
	$next_month = (($m+1)>12) ? 1 : $m+1;
	$next_year = (($m+1)>12) ? $y+1 : $y;
    print("<tr bgcolor=#7DAAD1><th align=left><a class='arrows' href='".$_SERVER['PHP_SELF']."?dept=$dept&m=$prev_month&y=$prev_year'><img src='btn_back.gif' border='0'></a></th>\n");
    print("<th><font color='FFFFFF' size=5>$month $y</font></th>\n");
    print("<th align=right><a class='arrows' href='".$_SERVER['PHP_SELF']."?dept=$dept&m=$next_month&y=$next_year'><img src='btn_next.gif' border='0'></a></th>\n");
?>
    </tr></table>
</td></tr>
<tr>
<td width=80 style='font-size:14px; font-weight:bold;' align='center'>Sun</td>
<td width=80 style='font-size:14px; font-weight:bold;' align='center'>Mon</td>
<td width=80 style='font-size:14px; font-weight:bold;' align='center'>Tue</td>
<td width=80 style='font-size:14px; font-weight:bold;' align='center'>Wed</td>
<td width=80 style='font-size:14px; font-weight:bold;' align='center'>Thu</td>
<td width=80 style='font-size:14px; font-weight:bold;' align='center'>Fri</td>
<td width=80 style='font-size:14px; font-weight:bold;' align='center'>Sat</td></tr>
<?php
    $d = 1;
    $wday = $firstwday;
    $firstweek = true;

    /*== loop through all the days of the month ==*/
    while ( $d <= $lastday) 
    {

        /*== set up blank days for first week ==*/
        if ($firstweek) {
            print "<tr valign=top>";
            for ($i=1; $i<=$firstwday; $i++) 
            { print "<td><font size=2>&nbsp;</font></td>"; }
            $firstweek = false;
        }

        /*== Sunday start week with <tr> ==*/
        if ($wday==0) { print "<tr valign=top>"; }

        /*== check for event ==*/  
	$match = $d.$m.$y;
	
	if(!strcmp($today,$match))
		$bgcolor='lightblue';
	else
		$bgcolor='white';
	
        print("<td height=60 class='tcell' bgcolor='$bgcolor'>");
        print("$d");
		if(isset($hours_by_day[$d]))
		{
			print("<br>");
			//print_r($hours_by_day[$d]);
			foreach($hours_by_day[$d] as $hours)
			{
				if($hours->closed == 1)
					print("Closed<br>$hours->exception_reason");
				else
					print("$hours->open_hour - $hours->close_hour");
			}
		}
        print("</td>\n");

        /*== Saturday end week with </tr> ==*/
        if ($wday==6) { print "</tr>\n"; }

        $wday++;
        $wday = $wday % 7;
        $d++;
    }
	
	// add empty cells for the remaining days of the final week
	while($wday > 0 && $wday < 7)
	{
		print("<td>&nbsp;</td>");
		$wday++;
	}
		
	
	print("</tr></table>\n");
} 




/*== get the last day of the month ==*/
function mk_getLastDayofMonth($mon,$year)
{
    for ($tday=28; $tday <= 31; $tday++) 
    {
        $tdate = getdate(mktime(0,0,0,$mon,$tday,$year));
        if ($tdate["mon"] != $mon) 
        { break; }

    }
    $tday--;

    return $tday;
}

?>
