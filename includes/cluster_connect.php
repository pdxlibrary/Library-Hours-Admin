<?php

require_once("includes/general_site_info.php");

function connect($db_host,$db_user,$db_pass,$db_name)
{
	if(!$link = mysql_connect($db_host, $db_user, $db_pass))
	{
		$result = 0;
		print("Error connecting to MySQL Server [$db_host] with user account [$db_user]!<br>\n");
	}
	else
	{
		if(!$conn = mysql_select_db($db_name,$link))
		{
			print("Error selecting database: $db_name<br>\n");
		}
	}
	return($link);
}

$conn_localhost = connect($cluster_database_host,$cluster_database_user,$cluster_database_pass,$cluster_database_name);
define("CONN_LOCALHOST",$conn_localhost);

?>
