<?php

	session_start();
	set_time_limit(60);
	require_once("includes/general_site_info.php");
	require_once("manage/includes/class.phpmailer.php");
	require_once("manage/includes/class.smtp.php");
	require_once("includes/connect_odin.php");

	function noDuplicate($table,$fields,$values)
	{
		global $conn_odin;
		
		$select = "select * from $table where ";
		for($i=0;$i<count($fields);$i++)
		{
			if($i==0)
				$select .= "$fields[$i] like '$values[$i]'";
			else
				$select .= " AND $fields[$i] like '$values[$i]'";
		}
		//print("select: $select<br>\n");
		$res = mysql_query($select,$conn_odin);
		$count = mysql_num_rows($res);
		//print("count: $count<br>\n");	
		if($count==0)
			return true;
		else
			return false;
	}


	function insert($table, $fields, $values)
	{
		global $conn_odin;
			
		$query = "INSERT INTO `$table` (";
		for($i=0; $i<count($fields); $i++)
		{
			$query .= "`" . $fields[$i] . "`";
			if($i+1 < count($fields))
				$query .= ",";
		}
		$query .= ") VALUES (";
		for($j=0; $j<count($values); $j++)
		{
			$query .= "'" . mysql_real_escape_string($values[$j]) . "'";
			if($j+1 < count($values))
				$query .= ",";
		}
		$query .= ")";
		//print("insert query: $query<br>\n");
		
		$res = mysql_query($query,$conn_odin);
			
		if(!$res)
		{
			print("There was an error inserting this record into the database.<br>\n");
			print("SQL Query: $query<br>\n");
		}
		//else
		//	addQuery("Insert",$username,$query);

		return mysql_insert_id();
	}


	function update($table, $field, $value, $where)
	{
		global $conn_odin;
		
		// TODO: check if most recent update is the same as the current update (reload bug)
		
		if(strcmp($where,''))
		{
			$query = "UPDATE `$table` SET `$field` = '$value' $where";
			//print("query: $query<br>\n");

			$res = mysql_query($query,$conn_odin);
			if(!$res)
			{
				print("There was an error updating this record.<br>\n");
				print("SQL Query: $query<br>\n");
				return 0;
			}
			else
			{
				return 1;
			}
		}
		return 0;
	}

?>