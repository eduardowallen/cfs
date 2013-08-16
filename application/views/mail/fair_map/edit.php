<script type="text/javascript">
	$(document).ready(function() {
		$('input[type="submit"]').click(function() {
			$('#loading').show();
		});
	});
</script>
<h1><?php echo $headline; ?></h1>
<form action="fairMap/edit/<?php echo $map_id; ?>/<?php echo $fair_id ?>" method="post" enctype="multipart/form-data">
	<label for="name"><?php echo $name_label; ?> *</label>
	<input type="text" name="name" id="name" value="<?php echo $mo->get('name') ?>"/>
	<label for="image"><?php echo $image_label; ?> (jpeg, jpg, gif, png, pdf - max 8mb)</label>
	<input type="file" name="image" id="image"/>
	<p><input type="submit" name="save" value="<?php echo $save_label; ?>"/></p>
	<p id="loading" class="hidden"><img src="images/icons/loading.gif" alt="loading..." style="width:100px;"/></p>
</form>