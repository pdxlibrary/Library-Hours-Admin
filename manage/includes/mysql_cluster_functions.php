<?php

	session_start();
	set_time_limit(60);
	require_once("includes/general_site_info.php");
	require_once("manage/includes/class.phpmailer.php");
	require_once("manage/includes/class.smtp.php");
	require_once("includes/cluster_connect.php");
					
	function insert($table, $fields, $values)
	{
		global $conn_localhost;
		global $error_report_address;
		global $error_report_name;
		
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
		$result->query = $query;
		//print("insert query: $query<br>\n");
		
		$insert_id = 0;
		
		$begin = @mysql_query("BEGIN",$conn_localhost);
		$res = @mysql_query($query,$conn_localhost);
		if(!$res) $result->success = false;
		$insert_id = @mysql_insert_id($conn_localhost);
	
		$result->insert_id = $insert_id;

		if($result->success !== false)
		{
			$commit = @mysql_query("COMMIT",$conn_localhost);
			$result->success = true;
		}
		else
		{
			$mail = new PHPMailer();

			$mail->Host     = "mailhost.pdx.edu";
			$mail->Mailer   = "smtp";

			$text_body = "ERROR REPORT\n\n";
			$text_body .= "QUERY: $query\n\n";
			if(count($result->error_messages)>0)
			{
				$text_body .= count($result->error_messages) . " ERROR MESSAGES:\n";
				foreach($result->error_messages as $em)
				{
						$text_body .= $em . "\n";
				}
			}

			$body  = nl2br($text_body);

			$mail->From     = "libsys@lists.pdx.edu";
			$mail->FromName = "Library Hours Manager";
			$mail->AddAddress($error_report_address, $error_report_name);
			
			$mail->Subject = "Library Hours Manager Query Error Report";
			$mail->Body    = $body;
			$mail->AltBody = $text_body;
			
			if(!$mail->Send())
				echo "There has been an email sending error.<br>";
			
			// rollback
			$rollback = @mysql_query("ROLLBACK",$conn_localhost);
			
			// insert error message
			if(!$res) $result->error_messages[] = "There was an error inserting into the database";
		}
		return($result);
	}


	function update($table, $field, $value, $where)
	{
		global $conn_localhost;
		global $error_report_address;
		global $error_report_name;
		
		if(strcmp($where,''))
		{
			$query = "UPDATE `$table` SET `$field` = '".mysql_real_escape_string($value)."' $where";
			$result->query = $query;
			//print("query: $query<br>\n");
			
			$begin = @mysql_query("BEGIN",$conn_localhost);
			
			$res = @mysql_query($query,$conn_localhost);
			if(!$res) $result->success = false;
				
			if($result->success !== false)
			{
				$commit = @mysql_query("COMMIT",$conn_localhost);
				$result->success = true;
			}
			else
			{				
				$mail = new PHPMailer();
				$mail->Host     = "mailhost.pdx.edu";
				$mail->Mailer   = "smtp";

				$text_body = "ERROR REPORT\n\n";
				$text_body .= "QUERY: $query\n\n";
				if(count($result->error_messages)>0)
				{
					$text_body .= count($result->error_messages) . " ERROR MESSAGES:\n";
					foreach($result->error_messages as $em)
					{
							$text_body .= $em . "\n";
					}
				}

				$body  = nl2br($text_body);

				$mail->From     = "libsys@lists.pdx.edu";
				$mail->FromName = "Library Hours Manager";
				$mail->AddAddress($error_report_address, $error_report_name);
				
				$mail->Subject = "Library Hours Manager Query Error Report";
				$mail->Body    = $body;
				$mail->AltBody = $text_body;
				
				if(!$mail->Send())
					echo "There has been an email sending error.<br>";

				$rollback = @mysql_query("ROLLBACK",$conn_localhost);
				
				// update error message
				if(!$res) $result->error_messages[] = "There was an error updating the database";
			}
			return($result);
		}
		else
			return null;
	}
	
	
	function delete($table,$where)
	{
		global $conn_localhost;
		global $error_report_address;
		global $error_report_name;
		
		if(strcmp($where,''))
		{
			$query = "DELETE FROM `$table` $where";
			//print("query: $query<br>\n");
			
			$begin = @mysql_query("BEGIN",$conn_localhost);
			$res = @mysql_query($query,$conn_localhost);
			if(!$res) $result->success = false;
					
			if($result->success !== false)
			{
				$commit = @mysql_query("COMMIT",$conn_localhost);
				$result->success = true;
			}
			else
			{
				$mail = new PHPMailer();
				$mail->Host     = "mailhost.pdx.edu";
				$mail->Mailer   = "smtp";

				$text_body = "ERROR REPORT\n\n";
				$text_body .= "QUERY: $query\n\n";
				if(count($result->error_messages)>0)
				{
					$text_body .= count($result->error_messages) . " ERROR MESSAGES:\n";
					foreach($result->error_messages as $em)
					{
							$text_body .= $em . "\n";
					}
				}
					
				$body  = nl2br($text_body);

				$mail->From     = "libsys@lists.pdx.edu";
				$mail->FromName = "Library Hours Manager";
				$mail->AddAddress($error_report_address, $error_report_name);
				
				$mail->Subject = "Library Hours Manager Query Error Report";
				$mail->Body    = $body;
				$mail->AltBody = $text_body;
				
				if(!$mail->Send())
					echo "There has been an email sending error.<br>";
				
				$rollback = @mysql_query("ROLLBACK",$conn_localhost);
				
				// delete error message
				if(!$res) $result->error_messages[] = "There was an error deleting from table [$table]";
			}
			return($result);
		}
		else
			return 0;
	}
	
	function print_errors($res)
	{
		print("<table width='90%' bgcolor='FFDDDD' style='border:3px solid #FF0000; color:red; font-size:12px; font-weight:bold;' cellpadding=10 cellspacing=0><tr><td>\n");
		foreach($res->error_messages as $error)
		{
			print("ERROR: $error<br>\n");
		}
		print("\nQUERY:\n");
		print(htmlentities($res->query));
		print("</pre>\n");
		print("</td></tr></table><br>\n");
	}
	
?>
