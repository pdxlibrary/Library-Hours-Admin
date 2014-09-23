<?php

class ldap_result
{
	var $success;
	var $firstname;
	var $lastname;
	var $email;
	var $edu_person_affiliations;
	var $server_status;
	var $errors;
	
	function ldap_result()
	{
		$this->success = false;
		$this->firstname = "";
		$this->lastname = "";
		$this->email = "";
		$this->edu_person_affiliations = array();
		$this->server_status = "";
		$this->errors = array();
	}
}

function ldap_authenticate($uid,$password,$server="ldaps://ldap.oit.pdx.edu",$port="636",$base_user_dn="ou=people,dc=pdx,dc=edu")
{
	if(!strcmp($password,'')) return(false);
	$ds=ldap_connect($server,$port);
	if (($link_id = @ldap_bind($ds, "uid=".$uid.",".$base_user_dn, $password)) == false)
	{
		ldap_close($ds);
		return(false);
	}
	else
	{
		ldap_close($ds);
		return(true);
	}
}

function ldap_lookup($uid,$password,$server="ldaps://ldap.oit.pdx.edu",$port="636",$base_dn="dc=pdx,dc=edu",$version="3")
{
	$ldap_result = new ldap_result();
	$ds=ldap_connect($server,$port);

	if ($ds)
	{
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $version);
		
		$r=@ldap_bind($ds);

		if($r)
		{
			$ldap_result->server_status = "UP";
			
			$sr=ldap_search($ds, $base_dn, "uid=".$uid);  

			$info = ldap_get_entries($ds, $sr);
			//print_r($info);
			
			if(ldap_count_entries($ds, $sr) < 1)
			{
				$ldap_result->errors[] = "Username not found.";
				$ldap_result->success = false;
			}
			else if(ldap_count_entries($ds, $sr) > 1)
			{
				$ldap_result->errors[] = "More than one user account with the same username found.";
				$ldap_result->success = false;
			}
			else if(($entry_id = ldap_first_entry($ds, $sr))== false)
			{
				$ldap_result->errors[] = "Entry of Search Result could not be fetched.";
				$ldap_result->success = false;
			}
			else if (( $user_dn = ldap_get_dn($ds, $entry_id)) == false)
			{
				$ldap_result->errors[] = "User-dn could not be fetched.";
				$ldap_result->success = false;
			}
			else if (($link_id = @ldap_bind($ds, $user_dn, $password)) == false)
			{
				$ldap_result->errors[] = "Username/Password are incorrect.";
				$ldap_result->success = false;
			}
			else if(!strcmp($password,''))
			{
				$ldap_result->errors[] = "Username/Password are incorrect.";
				$ldap_result->success = false;
			}
			else
			{
				$ldap_result->success = true;

				$ldap_result->firstname = $info[0]['givenname'][0];
				$ldap_result->lastname = $info[0]['sn'][0];
				$ldap_result->email = $info[0]['mail'][0];
				foreach($info[0]['edupersonaffiliation'] as $var => $val)
				{
					if(strcmp($var,'count'))
						$ldap_result->edu_person_affiliations[] = $val;
				}
			}
		}
		else
		{
			$ldap_result->success = false;
			$ldap_result->server_status = "DOWN";
			$ldap_result->errors[] = "Could not bind to LDAP Server.";
		}
		
		ldap_close($ds);
	}
	else
	{
		$ldap_result->success = false;
		$ldap_result->server_status = "DOWN";
		$ldap_result->errors[] = "Could not connect to LDAP Server.";
	}
	
	return ($ldap_result);

}

function ldap_lookup_simple($uid,$password,$server="ldaps://ldap.oit.pdx.edu",$port="636",$base_dn="dc=pdx,dc=edu",$version="3")
{
	$ldap_result = new ldap_result();
	$ds=ldap_connect($server,$port);

	if ($ds)
	{
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $version);
		
		$r=@ldap_bind($ds);

		if($r)
		{
			$ldap_result->server_status = "UP";
			
			$sr=ldap_search($ds, $base_dn, "uid=".$uid);  

			$info = ldap_get_entries($ds, $sr);
			print("<pre>\n");
			print_r($info);
			print("</pre>\n");
			$count = ldap_count_entries($ds,$sr);
			print("Number of matches for uid=$uid: $count<br>\n");
		}
		else
		{
			$ldap_result->success = false;
			$ldap_result->server_status = "DOWN";
			$ldap_result->errors[] = "Could not bind to LDAP Server.";
		}
		
		ldap_close($ds);
	}
	else
	{
		$ldap_result->success = false;
		$ldap_result->server_status = "DOWN";
		$ldap_result->errors[] = "Could not connect to LDAP Server.";
	}

	return ($ldap_result);
}

// example usage:
/*
$username = "erselle";
$password = "";
$result = ldap_lookup($username,$password);
print_r($result);
if(ldap_authenticate($username,$password))
	print("success!!<br>\n");
else
	print("failed!!<br>\n");
*/

?>