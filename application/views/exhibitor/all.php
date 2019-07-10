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
			})/*.success(function(responseData){
				showInfoDialog(responseData.result);
			}).error(function(responseData){
				showInfoDialog('<?php echo uh($translator->{'Error: Could not resend user details!'}); ?><br/>' + responseData.mail_errors + '<br/>' + responseData.errors);
			})*/;
			showInfoDialog('The user\'s password was reset and a mail was sent');
		}
	}
/*
	$(document).ready( function () {
		$('#exhibitors_list').DataTable();
		$('#exhibitors_list').show();
	});
*/
	function deleteAccount(id, name, row){
			$.confirm({
				title: ' ',
				content: 'Vill du verkligen radera användare ' + name + '?',
				confirm: function(){
					$.ajax({
						url: 'exhibitor/deleteAccount/' + id,
						method: 'POST',
						success: function(response) {
							if (response.success == 'true') {
								var table = $('#exhibitors_list').DataTable();
								table
								.row( $(row).parents('tr') )
								.remove()
								.draw();
							}
							$.alert({
								content: response.result + ' ' + name
							});
						}
					});
				},
				cancel: function() {
				}
			});
	}
</script>
<style>
#popupform {
	padding:20px;
	position:absolute;
	z-index:99999;
	background:#ffffff;
	width:300px;
	height:auto;
	text-align:left;
	left:50%;
	top:300px;
	margin:0 0 0 -154px;
	border:4px solid #333333;
	-moz-border-radius:8px;
	-webkit-border-radius:8px;
	border-radius:8px;
}
#popupform div {
	width: 200px;
	position: absolute;
	left: 50%;
	margin-left: -100px;
	margin-top: 20px;
}
#popupform p {
	text-align:center;
}
	.squaredFour{width:1.416em; height:1.416em;}
	.squaredFour:before{left:0.33em;top:0.33em;}
</style>
<script type="text/javascript" src="js/tablesearch.js<?php echo $unique?>"></script>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="user/edit/new/1"><?php echo $create_link; ?></a></p>
<br />
	<form method="post">
		<div class="floatright right">
			<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="exhibitors_list" data-fair="1337"></button>
		</div>
	<table class="std_table use-scrolltable" id="exhibitors_list">
		<thead>
			<tr>
				<th><?php echo $th_company ?></th>
				<th><?php echo $th_orgnr ?></th>
				<th><?php echo $th_name ?></th>
				<th><?php echo $th_commodity ?></th>
				<th><?php echo $th_city ?></th>
				<th><?php echo $th_phone ?></th>
				<th><?php echo $th_email ?></th>
				<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $th_last_login ?></th>
				<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $th_created ?></th>
				<th><?php echo $th_edit ?></th>
				<th><?php echo $th_delete ?></th>
				<th><?php echo $th_resend ?></th>
				<th class="last" data-sorter="false">
					<input type="checkbox" class="check-all" data-group="rows" />
					<label class="squaredFour" for="check-all" />
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user): ?>
			<tr>
				<td><?php echo $user->get('company'); ?></td>
				<td><?php echo $user->get('orgnr'); ?></td>
				<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>" class="showProfileLink"><?php echo $user->get('name'); ?></a></td>
				<td><?php echo $user->get('commodity'); ?></td>
				<td><?php echo $user->get('city'); ?></td>
				<td><?php echo $user->get('contact_phone2'); ?></td>
				<td><?php echo $user->get('email'); ?></td>
				<td><?php echo date('d-m-Y H:i:s', $user->get('last_login')); ?></td>
				<td><?php echo date('d-m-Y H:i:s', $user->get('created')); ?></td>
				<td class="center"><a href="user/edit/<?php echo $user->get('id') ?>"><img src="images/icons/pencil.png" class="icon_img" alt="" title="<?php echo $th_edit; ?>"/></a></td>
				<td class="center"><a onclick="deleteAccount(<?php echo $user->get('id') ?>, '<?php echo htmlspecialchars($user->get('name')); ?> <?php echo htmlspecialchars($user->get('company')); ?>', this)"><img src="images/icons/delete.png" class="icon_img" alt="" title="<?php echo $th_delete; ?>" /></a></td>
				<td class="center"><a onclick="resendDetails(<?php echo $user->get('id') ?>, '<?php echo htmlspecialchars($user->get('name')); ?>')"><img src="images/icons/support.png" class="icon_img" alt="" title="<?php echo $th_resend; ?>" /></a></td>

				<td><input type="checkbox" name="rows[]" class="rows" value="<?php echo $user->get('id'); ?>" data-userid="<?php echo $user->get('id'); ?>" /><label class="squaredFour" for="<?php echo $user->get('id'); ?>" /></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>