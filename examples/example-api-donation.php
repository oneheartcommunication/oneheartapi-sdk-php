<?php
	include("Oneheart_apiclient.php");
	$api = new Oneheart_apiclient("my@public.key", "my_secret_key");
	
	// Here we want the user name of the user #40
	$url = $api->users->donate(40, "First name", "Last name", "my@email.com", "7 Main street", "00000", "aaa", "France", 1, "EUR", "http://example.com/aaa", "http://example.com/bbb", "http://example.com/ccc");
	
	var_dump($url);
?>