<?php

if (isset($loggedin)) {
	echo '<h1>'.$loggedin.'</h1>';
	exit;
}

?>
<h1><?php echo $headline; ?></h1>
<?php if( isset($first_time_msg) ) : ?>
	<p><b><?php echo $first_time_msg; ?></b></p>
<?php endif; ?>
<?php if( isset($confirmed_msg) ) : ?>
	<p><b><?php echo $confirmed_msg; ?></b></p>
<?php endif; ?>
<form action="user/login<?php echo ($fair_url != '') ? '/'.$fair_url : ''; ?>" method="post">
	<p class="error"><?php echo $error; ?></p>
	<p><label for="user"><?php echo $user_name; ?></label>
	<input type="text" name="user" id="user"/></p>
	<p><label for="pass"><?php echo $password; ?></label>
	<input type="password" name="pass" id="pass"/></p>
	<p><input type="submit" name="login" value="<?php echo $button; ?>"/></p>
	<p><a href="user/resetPassword"><?php echo $forgotlink; ?></a></p>

</form>
