<?php
	include("Oneheart_apiclient.php");
	$api = new Oneheart_apiclient("test@test.com", "y2B427807W821d23v8Y8 ");
	
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
	<?php foreach($spots as $spot) { ?>
    <li>#<?php echo $spot["id"]; ?>: <?php echo $spot["name"]; ?></li>
    <?php } ?>
</ul>