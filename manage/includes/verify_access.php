<?php


// import phpCAS lib
include_once('CAS.php');

phpCAS::setDebug();

// initialize phpCAS
phpCAS::client(SAML_VERSION_1_1,'sso.pdx.edu',443,'/cas');

// no SSL validation for the CAS server
phpCAS::setNoCasServerValidation();

// check to see if the user is alread authenticated
if(!phpCAS::checkAuthentication())
{
	// force CAS authentication
	phpCAS::forceAuthentication();
}

$allowed_admins = array();
$allowed_admins[] = "mflakus";
$allowed_admins[] = "mealey";
$allowed_admins[] = "cgeib";
$allowed_admins[] = "blalockk";
$allowed_admins[] = "bowman";
$allowed_admins[] = "westonc";

$username = phpCAS::getUser();

if(!in_array($username,$allowed_admins))
{
	print("Access Denied for: ");
	print(phpCAS::getUser());
	exit();
}

// logout if desired
if (isset($_REQUEST['logout'])) {
	phpCAS::logout();
}

?>