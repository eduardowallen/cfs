<script>
form_register = '<?php echo Form::LoadForJS("userdata", array('popup'=>true, "action"=>"user/register".(isset($fair_url)?'/'.$fair_url:''))); ?>';
</script>
<style>
#popupform_login {
	width: 570px;
	height:510px;
}
#content {
	padding: 5em 20em;
}
form {
	margin-left:10em;
}
</style>
<?php

if (isset($loggedin)) {
	echo '<h1>'.$loggedin.'</h1>';
	exit;
}

?>
<img alt="Chartbooker International Fair System" src="images/logo/chartbooking_logo_large.png" style="width:30em; margin-left:5em;">
<div style="margin-top: 1em;">
<?php if( isset($first_time_msg) && isset($first_time_title) ) : ?>
	<script>
		showInfoDialog('<?php echo $first_time_msg; ?>', '<?php echo $first_time_title; ?>');
	</script>
<?php endif; ?>
<?php if( isset($confirmed_msg) ) : ?>
	<p><b><?php echo $confirmed_msg; ?></b></p>
<?php endif; ?>
<form action="user/login<?php echo ($fair_url != '') ? '/'.$fair_url : ''; ?>" method="post">
	<br/>
	<p class="error">
		<?php echo $error; ?>
	</p>
	<p>
		<label for="user"><?php echo $user_name; ?></label>
		<input type="text" name="user" id="user"/>
	</p>
	<p class="nomargin">
		<label for="pass"><?php echo $password; ?></label>
		<input type="password" name="pass" id="pass"/>
	</p>
	<p style="text-align:center; width:20.833em;" class="nomargin">
		<input type="submit" name="login" value="<?php echo uh($translator->{"Log in"}); ?>" class="greenbutton bigbutton" />
	</p>
	<p style="text-align:center; width:20.833em;">
	<input type="button" name="register" class="registerlink bluebutton bigbutton" value="<?php echo uh($translator->{'Register'}); ?>" />
	</p>

	<p style="color:#116734; font-size:1.16em; font-weight:bold; margin-left:1em;">
		<a href="user/resetPassword"><?php echo $forgotlink; ?></a>
	</p>
</form>
	<p <?php if (isset($good)) {
		if ($good == "yes")
			echo 'style="color:#168912; text-align:center;"';
		else if ($good == "no")
			echo 'style="color:#F00; text-align:center;"';
		}
	?>><?php if (isset($res_msg)) echo $res_msg; ?></p>	
</div>
