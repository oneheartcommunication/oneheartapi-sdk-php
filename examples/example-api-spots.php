<?php
	include("Oneheart_apiclient.php");
	$api = new Oneheart_apiclient("my@public.key", "my_secret_key");
	
	// Get the first 10 spots by id
	$spots = $api->spots->summary(
		0,
		10,
		array("id","name"),
		"-name"
	);
?>
<p>Here's a list of the 10 firsts spots:</p>
<ul>
	<?php foreach($spots["datas"] as $spot) { ?>
    <li>#<?php echo $spot["id"]; ?>: <?php echo $spot["name"]; ?></li>
    <?php } ?>
</ul>