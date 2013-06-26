<h1><?php echo $headline; ?></h1>
<p> <?php echo $line1?> <br />  <?php echo $line2?> </p>
<form action="user/resetPassword" method="post">
	<?php if (isset($new_pass)): ?>
	
	<p><?php echo $translator->{'Your new password is '}; ?>
	<strong><?php echo $new_pass; ?></strong><br/>
	<a href="user/login"><?php echo $translator->{'Sign in to change it.'}; ?></p></a>
	
	<?php else: ?>
	<p class="error"><?php echo $error; ?></p>
	<p class="ok"><?php echo $ok; ?></p>
	<p><label for="user"><?php echo $user_name.' / '.$email;?></label>
	<input type="text" name="user" id="user"/></p>
	<p><input type="submit" name="send" value="<?php echo $button; ?>"/></p>

	<?php endif; ?>
</form>
<p><a href="<?php echo BASE_URL.'user/login'?>"><button onclick="goBack()"><?php echo $goback; ?></button></a></p>
