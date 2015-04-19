<?php
// Load a simpler form for administrators
if (userLevel() == 2){
echo Form::Load("userdata_administrator",
	array(
		'headline'=>$translator->{'Account settings'},
		'action'=>"user/accountSettings",
		'user'=>$user,
		'error'=>@$error
	  )
  );
} else {
echo Form::Load("userdata",
	array(
		'headline'=>$translator->{'Account settings'},
		'action'=>"user/accountSettings",
		'user'=>$user,
		'error'=>@$error
	  )
  );
}
?>