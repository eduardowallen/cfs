<h1><?php echo $headline; ?></h1>
<form action="fairMap/create/<?php echo $fair; ?>" method="post" enctype="multipart/form-data">
	<label for="name"><?php echo $name_label; ?> *</label>
	<input type="text" name="name" id="name"/>
	<label for="image"><?php echo $image_label; ?> (jpeg, jpg, gif, png, pdf - max 8mb)</label>
	<input type="file" name="image" id="image"/>
	<input type="hidden" name="fairId" value="<?php echo $fair?>" />
	<p><input type="submit" name="create" value="<?php echo $save_label; ?>"/></p>
</form>
