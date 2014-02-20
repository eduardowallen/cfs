<h1><?php echo $headline; ?></h1>
<form action="user/changePassword" method="post">
	
	<p><?php echo $info; ?></p>
	<p class="floatright" style="width:200px; background:#efefef; border:1px solid #b1b1b1; padding:10px; margin-right:240px;"><?php echo $pass_standard ?></p>
	<p class="error"><?php echo $error; ?></p>
	<p class="ok"><?php echo $ok; ?></p>
	
	<label for="password"><?php echo $password_old_label; ?> *</label>
	<input type="password" name="password_old" id="password_old"/>
	
	<label for="password"><?php echo $password_label; ?> *</label>
	<input type="password" name="password" id="password" class="hasIndicator"/>
	
	<label for="password_repeat"><?php echo $password_repeat_label; ?> *</label>
	<input type="password" name="password_repeat" id="password_repeat"/>
	
	<p><input type="submit" name="save" value="<?php echo $save_label; ?>" class="save-btn" /></p>
	
</form>