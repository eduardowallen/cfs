<h1><?php echo $headline; ?></h1>

<form action="exhibitor/edit/<?php echo $fairId; ?>/<?php echo $userId; ?>" method="post">
	<label for="category"><?php echo $cat_label; ?></label>
	<select name="category" id="category">
		<?php echo $cat_options; ?>
	</select>
	<input type="submit" name="save" value="<?php echo $save_label; ?>"/>
</form>