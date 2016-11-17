<script>
form_register = '<?php echo Form::LoadForJS("userdata", array('popup'=>true, "action"=>"user/register".(isset($fair_url)?'/'.$fair_url:''))); ?>';
</script>
<style>
#popupform_login {
	width: 570px;
	height:510px;
}
</style>
<?php
echo Form::Load("userdata",
  array(
	  'headline'=>$translator->{'Register'},
	  'action'=>"user/register".(isset($fair_url)?'/'.$fair_url:''),
	  'error'=>$error,
	  'user'=>$user
	)
);
?>