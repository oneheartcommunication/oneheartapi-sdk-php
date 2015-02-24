<?php
	include("Oneheart_apiclient.php");
	$api = new Oneheart_apiclient("my@public.key", "my_secret_key");
	
	// Add a spot
	$datas = array(
		"name"=>"Test",
		"description"=>"Test",
		"label1"=>"Recyclage",
		"theme"=>"Action sociale",
		"keywords"=>"key,word",
		"latitude"=>"51.5130836",
		"longitude"=>"-0.1243961",
		"address"=>"112 Long Acre London WC2E, United Kingdom"
	);
	$response = $api->spots->submit($datas, "71d3403myz803XKm6x68c1bb72FQkz1CTj3r8Zy1");
?>
Spot #<?php echo $response["id"]; ?> was added;