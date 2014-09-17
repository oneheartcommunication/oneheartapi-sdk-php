<?php
	include("Oneheart_apiclient.php");
	$api = new Oneheart_apiclient("test@test.com", "y2B427807W821d23v8Y8 ");
	
	// Here we want the user name of the user #211
	$user = $api->users->single(211, array("username"));
?>

The user #211 is <?php echo $user["username"] ?>