<h1><?php echo $headline; ?></h1>
<p> <?php echo $line1?> <br />  <?php echo $line2?> </p>
<form action="user/resetPassword/backref/<?php echo $go_back_url; ?>" method="post">
	<?php if (isset($new_pass)): ?>
	
	<p><?php echo uh($translator->{'Your new password is '}); ?>
	<strong><?php echo $new_pass; ?></strong><br/>
	<a href="user/login"><?php echo uh($translator->{'Sign in to change it.'}); ?></p></a>
	
	<?php else: ?>
	<p class="error"><?php echo $error; ?></p>
	<p class="ok"><?php echo $ok; ?></p>
	<p><label for="user"><?php echo $user_name.' / '.$email;?></label>
	<input type="text" name="user" id="user"/></p>
	<p><input type="submit" name="send" value="<?php echo $button; ?>" class="save-btn2"/></p>

	<?php endif; ?>
</form>
<p><a href="<?php echo $go_back_url; ?>" class="link-button"><?php echo $goback; ?></a></p>
