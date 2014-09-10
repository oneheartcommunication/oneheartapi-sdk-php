<?php
	include("Oneheart_apiclient.php");
	$api = new Oneheart_apiclient("", "");
	
	// Here we want the user name of the first user
	$users = $api->users->summary(
		0,
		1,
		array("username")
	);
	
	// Here we want the user name of the user #211
	$user = $api->users->single(211);
	
	// And now, we generate a donation URL for the user #40 (ELA)
	$donation = $api->users->donate(40, "Teddy", "Gandon", "teddy@agenceoneheart.com", "28 rue Jean Stas", "1060", "Bruxelles", "Belgique", 2, "EUR", "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]."?action=success", "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]."?action=fail", "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]."?action=cancel");
	var_dump($donation);
	
	// And now, we generate a donation URL for the user #40 (ELA) for a monthly donation
	$donation_month = $api->users->donate(40, "Teddy", "Gandon", "teddy@agenceoneheart.com", "28 rue Jean Stas", "1060", "Bruxelles", "Belgique", 2, "EUR", "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]."?action=success", "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]."?action=fail", "http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]."?action=cancel", "", TRUE);
?>

The first user was <?php echo $users[0]["username"] ?><br />
The user #211 is <?php echo $user["username"] ?><br />
To donate to user #40, <a href="<?php echo($donation["url"]); ?>" target="_blank">click here</a><br />
To donate to user #40 each month, <a href="<?php echo($donation_month["url"]); ?>" target="_blank">click here</a>