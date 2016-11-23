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
<form action="user/forgotUsername" method="post">
	
	<?php if (isset($usermessage) && !isset($error)): ?>

		<p class="ok"><?php echo $usermessage; ?></p>
	
	<?php else: ?>
	<?php if (isset($usermessage)): ?>
		<?php if (isset($error)): ?>
		<p class="error"><?php echo $usermessage; ?></p>
		<?php else: ?>
		<p class="ok"><?php echo $usermessage; ?></p>
		<?php endif; ?>
	<?php endif; ?>
	<p><label for="user"><?php echo $email; ?></label>
	<input type="text" name="email" id="email"/></p>
	<p><input type="submit" name="remindme" value="<?php echo $remindme; ?>"/></p>
	<?php endif; ?>
</form>