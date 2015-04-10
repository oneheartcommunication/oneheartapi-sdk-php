<?php
	include("Oneheart_apiclient.php");
	$api = new Oneheart_apiclient("my@public.key", "my_secret_key");
	
	// Insights for widget videos
	$insights = $api->insights->get(
		"oauth_token",
		array("video:widget"),
		array("analytics.users","post.theme"),
		"post.theme"
	);
	
	var_dump($insights);
?>