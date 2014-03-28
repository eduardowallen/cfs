<script type="text/javascript" src="js/tablesearch.js"></script>
<script type="text/javascript">
	function destroyPopup(){
		$('#overlay').remove();
		$('#popupform').remove();
	}

	function resendDetails(id, name){
		if(confirm("<?php echo uh($translator->{'Really reset user password'}); ?> " + name + "?") == true){
			$.ajax({
				url: 'user/resendDetails/'+id, 
				type: 'GET'
			}).success(function(responseData){

				$('body').append('<div id="overlay"></div><div id="popupform"><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue">'+responseData.result+'</div>');
				$('#overlay').css('display', 'block');

				$('#overlay').bind('click',function(){
					destroyPopup();
				});

				$('.closeDialogue').bind('click', function(){
					destroyPopup();
				});
			}).error(function(){
				$('body').append('<div id="overlay"></div><div id="popupform"><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"><p style="color:#f00;"><?php echo uh($translator->{'Error: Could not resend user details!'}); ?></p></div>');
				$('#overlay').css('display', 'block');

				$('#overlay').bind('click',function(){
					destroyPopup();
				});

				$('.closeDialogue').bind('click', function(){
					destroyPopup();
				});
			});	
		}
	}
</script>

<style>
	#popupform{padding:20px;}
</style>
<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="user/edit/new/1"><?php echo $create_link; ?></a></p>
<p><a class="button add" href="mailto:<?php
	$count=0;
	foreach ($users as $user): 
		if($count == 0):
			echo "?bcc=".$user->get('email');
		else:
			echo "&bcc=".$user->get('email');
		endif;
		$count++;
	endforeach;?>"><?php echo uh($translator->{'Send mail'}); ?></a></p>
<div class="scrolltbl onlythirteen">
	<table class="std_table">
		<thead>
			<tr>
				<th><?php echo $th_company ?></th>
				<th><?php echo $th_orgnr ?></th>
				<th><?php echo $th_name ?></th>
				<th><?php echo $th_phone ?></th>
				<th><?php echo $th_email ?></th>
				<th><?php echo $th_fairs ?></th>
				<th><?php echo $th_bookings ?></th>
				<th><?php echo $th_last_login ?></th>
				<th><?php echo $th_created ?></th>
				<th><?php echo $th_edit ?></th>
				<th><?php echo $th_delete ?></th>
				<th><?php echo $th_resend ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user): ?>
			<tr>
				<td><?php echo $user->get('company'); ?></td>
				<td><?php echo $user->get('orgnr'); ?></td>
				<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('name'); ?></a></td>
				<td><?php echo $user->get('phone1'); ?></td>
				<td><?php echo $user->get('email'); ?></td>
				<td class="center"><?php echo $user->get('fair_count'); ?></td>
				<td class="center"><?php echo $user->get('ex_count'); ?></td>
				<td><?php echo date('d-m-Y H:i:s', $user->get('last_login')); ?></td>
				<td><?php echo date('d-m-Y H:i:s', $user->get('created')); ?></td>
				<td class="center"><a href="user/edit/<?php echo $user->get('id') ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo uh($translator->{'Edit'}); ?>"/></a></td>
				<td class="center"><a href="exhibitor/deleteExhibitor/<?php echo $user->get('id'); ?>/no/all"><img src="images/icons/delete.png" alt=""/></a></td>
				<td class="center"><a onclick="resendDetails(<?php echo $user->get('id') ?>, '<?php echo htmlspecialchars($user->get('name')); ?>')"> <img src="images/icons/delete.png" alt=""/></a></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
