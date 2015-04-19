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