<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="user/edit/new<?php if (isset($createLinkType) && $createLinkType > 0) { echo '/'.$createLinkType; } ?>"><?php echo $create_link; ?></a></p>
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
				<!--<th><?php echo $th_level; ?></th>-->
				<th><?php echo $th_user; ?></th>
				<th><?php echo $th_email; ?></th>
				<th><?php echo $th_phone; ?></th>
				<th>Last login</th>
				<th>Created</th>				
				<th><?php echo $th_edit; ?></th>
				<th><?php echo $th_delete; ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user): ?>
			<tr>
				<!--<td class="center"><?php echo $user->get('level'); ?></td>-->
				<td><?php echo $user->get('name'); ?></td>
				<td><?php echo $user->get('email'); ?></td>
				<td><?php echo $user->get('phone1'); ?></td>
				<td><?php echo date('d-m-Y H:i:s', $user->get('last_login')); ?></td>
				<td><?php echo date('d-m-Y H:i:s', $user->get('created')); ?></td>
				<td class="center"><a href="user/edit/<?php echo $user->get('id'); ?>/<?php echo $user->get('level') ?>"><img src="images/icons/pencil.png" alt="" title="Edit"/></a></td>
				<td class="center"><a onclick="confirmBox(event, 'Vill du verkligen ta bort master <?php echo $user->get('name') ?>', 'user/delete/<?php echo $user->get('id'); ?>/confirmed')" href="user/delete/<?php echo $user->get('id'); ?>"><img src="images/icons/delete.png" alt="" title="Delete"/></a></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
