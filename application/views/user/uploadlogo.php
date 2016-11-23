<script type="text/javascript">
	$(document).ready(function() {
		$('input[type="submit"]').click(function() {
			$('#loading').show();
		});
	});
</script>
<button class="go_back" onclick="location.href='../user/accountSettings'"><?php echo uh($translator->{'Go back'}); ?></button>

<br />

<h1><?php echo $headline; ?></h1>


<?php foreach(glob(ROOT.'public/images/exhibitors/'.$user.'/*') as $filename) : ?>
	<img src="<?php echo($image_path. '/' . basename($filename) . "\n"); ?>"/>
	<a style="padding-left:30px;" onclick="return confirm('Really delete?');" href="user/deletelogo"><img src="images/icons/delete.png" class="icon_img" alt="" title="<?php echo $delete; ?>" /></a>
<?php endforeach; ?>

<form action="user/uploadlogo" method="post" enctype="multipart/form-data">
	<label for="image"><?php echo $image_label; ?> <?php echo uh($translator->{'(jpeg, jpg, gif, png, pdf - max 8mb)'}); ?></label>
	<input type="file" name="image" id="image"/>
	<p><input type="submit" name="submit" value="<?php echo $save_label; ?>" class="greenbutton mediumbutton" /></p>
	<?php echo $error_notimg; ?>
	<?php echo $error_toobig; ?>
	<?php echo $error_wrongformat; ?>
	<?php echo $error_notuploaded; ?>
	<?php echo $error_whenuploaded; ?>
	<?php echo $img_wasuploaded; ?>
	<p id="loading" style="display:none"><img src="images/icons/loading.gif" alt="loading..." style="width:100px;"/></p>
</form>