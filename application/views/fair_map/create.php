<script type="text/javascript">
	$(document).ready(function() {
		$('input[type="submit"]').click(function() {
			$('#loading').show();
		});
	});
</script>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/maps/<?php echo $fairId; ?>'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $headline; ?> <?php echo $fair->get('name'); ?></h1>
<form action="fairMap/create/<?php echo $fairId; ?>" method="post" enctype="multipart/form-data">
	<label for="name"><?php echo $name_label; ?> *</label>
	<input type="text" name="name" id="name"/>
	<label for="image"><?php echo $image_label; ?> (jpeg, jpg, gif, png, pdf - max 8mb)</label>
	<input type="file" name="image" id="image"/>
	<p><input type="submit" name="create" value="<?php echo $save_label; ?>" class="greenbutton mediumbutton" /></p>
	<p id="loading" class="hidden"><img src="images/icons/loading.gif" alt="loading..." style="width:100px;"/></p>
</form>