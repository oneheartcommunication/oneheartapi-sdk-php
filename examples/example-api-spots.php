<?php
	include("Oneheart_apiclient.php");
	$api = new Oneheart_apiclient("", "");
	
	// Here we want the user name of the first user
	$spots = $api->spots->summary(
		0,
		50,
		array("username")
	);
	
	var_dump($spots);
?>