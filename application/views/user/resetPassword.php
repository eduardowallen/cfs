<script>
form_register = '<?php echo Form::LoadForJS("userdata", array('popup'=>true, "action"=>"user/register".(isset($fair_url)?'/'.$fair_url:''))); ?>';
</script>
<style>
#popupform_login {
	width: 570px;
	height:510px;
}
</style>
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
	<p><input type="submit" name="send" style="margin-left: 0;" value="<?php echo $button; ?>" class="greenbutton mediumbutton"/></p>

	<?php endif; ?>
</form>
<p><a href="<?php echo $go_back_url; ?>" class="link-button bluebutton"><?php echo $goback; ?></a></p>
