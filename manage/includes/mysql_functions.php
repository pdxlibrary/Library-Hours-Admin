<?php

	function noDuplicate($table,$fields,$values)
	{
		$select = "select * from $table where ";
		for($i=0;$i<count($fields);$i++)
		{
			if($i==0)
				$select .= "$fields[$i] like '$values[$i]'";
			else
				$select .= " AND $fields[$i] like '$values[$i]'";
		}
		//print("select: $select<br>\n");
		$res = mysql_query($select);
		$count = mysql_num_rows($res);
		//print("count: $count<br>\n");	
		if($count==0)
			return true;
		else
			return false;
	}


	function insert($table, $fields, $values)
	{
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
			$query .= "'" . $values[$j] . "'";
			if($j+1 < count($values))
				$query .= ",";
		}
		$query .= ")";
		//print("insert query: $query<br>\n");
		
		$res = mysql_query($query);
			
		if(!$res)
		{
			print("There was an error inserting this record into the database.<br>\n");
			print("SQL Query: $query<br>\n");
		}
		//else
		//	addQuery("Insert",$username,$query);

		return mysql_insert_id();
	}

//	function delete($table, $where)
//	{
//		global $username;
//		$query = "DELETE FROM `$table` WHERE $where";
//
//		print("delete query: $query<br>\n");
//	}

	function update($table, $field, $value, $where)
	{
		global $username;
		// TODO: check if most recent update is the same as the current update (reload bug)
		
		if(strcmp($where,''))
		{
			$query = "UPDATE `$table` SET `$field` = '$value' $where";
			//print("query: $query<br>\n");

			$res = mysql_query($query);
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

	function select($table, $fields, $where, $order, $limit)
	{
		$query = "SELECT ";
		for($i=0; $i<count($fields); $i++)
		{
			$query .= "`" . $fields[$i] . "`";
			if($i+1 < count($fields))
				$query .= ",";
		}
		$query .= " FROM `$table` $where $order $limit";
		print("select: $query<br>\n");
	}

	//$val[] = 'mike';
	//$val[]='flakus';
	//$name[]='first_name';
	//$name[]='last_name';
	//insert('clients', $name, $val);
	//update('clients','last_name','flakusenski',"WHERE last_name like 'flakus'");
	//select('clients',$name,"WHERE `last_name` like 'flakus'",'','');
?>