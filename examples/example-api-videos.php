<?php
	include("Oneheart_apiclient.php");
	$api = new Oneheart_apiclient("my@public.key", "my_secret_key");
	
	// Get the first 10 videos by id
	$videos = $api->videos->summary(
		0,
		10,
		array("id","name"),
		"-id"
	);
?>
<p>Here's a list of the 10 latest videos:</p>
<ul>
	<?php foreach($videos as $video) { ?>
    <li>#<?php echo $video["id"]; ?>: <?php echo $video["name"]; ?> (<a href="?id=<?php echo $video["id"]; ?>">get stream URLs</a>)</li>
    <?php } ?>
</ul>

<?php 
	if(isset($_GET["id"])) {
		$streams_urls = $api->videos->watch($_GET["id"]);
?>
<p>
<a href="<?php echo $streams_urls["mp4_h264_aac_hq"]; ?>">
	<img src="<?php echo $streams_urls["jpeg_thumbnail_large"]; ?>" />
</a>
</p>
<p>Click on the image above to view the video.</p>
<?php 
	} 
?>