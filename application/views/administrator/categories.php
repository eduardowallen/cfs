<h1><?php echo $headline; ?></h1>

<form action="administrator/categories" method="post" style="background:#efefef; border:1px solid #b1b1b1; padding:20px;">
	<h3><?php echo $form_headline; ?></h3>
	<label for="name"><?php echo $name_label; ?> *</label>
	<input type="text" name="name" id="name"/>
	<input type="submit" name="save" value="<?php echo $save_label; ?>"/>
</form>