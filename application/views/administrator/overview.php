<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="administrator/edit/new/<?php echo $fair; ?>"><?php echo $create_link; ?></a></p>

<?php if(count($users) > 0) : ?>
<p><a class="button add" href="mailto:<?php
	$count=0;
	foreach ($users as $user): 
		if($count == 0):
			echo "?bcc=".$user->get('email');
		else:
			echo "&bcc=".$user->get('email');
		endif;
		$count++;
	endforeach;?>"><?php echo $translator->{'Send mail'}?></a></p>
	<div class="tbld">
		<table class="std_table">
			<thead>
				<tr>
					<th><?php echo $th_account_status; ?></th>
					<th><?php echo $th_user; ?></th>
					<th><?php echo $th_spots_created; ?></th>
					<th><?php echo $th_last_login; ?></th>
					<th><?php echo $th_total_logins; ?></th>
					<th><?php echo $th_account_created; ?></th>
					<th><?php echo $th_edit; ?></th>
					<th><?php echo $th_delete; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($users as $user): ?>
				<tr>
					<td class="center"><?php echo ($user->get('locked') == 1) ? $status_locked : $status_active; ?></td>
					<td><?php echo $user->get('name'); ?></td>
					<td class="center"><?php echo $user->get('spots_created'); ?></td>
					<td class="center"><?php if ($user->get('last_login')) { echo date('d-m-Y H:i:s', $user->get('last_login')); } ?></td>
					<td class="center"><?php echo $user->get('total_logins'); ?></td>
					<td class="center"><?php echo date('d-m-Y H:i:s', $user->get('created')); ?></td>
					<td class="center"><a href="administrator/edit/<?php echo $user->get('id').'/'.$thisFair;?>"><img src="images/icons/pencil.png" alt="" title="Edit"/></a></td>
					<td class="center"><a href="administrator/delete/<?php echo $user->get('id'); ?>"><img src="images/icons/delete.png" alt="" title="Delete"/></a></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
