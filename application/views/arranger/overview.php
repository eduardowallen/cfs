<script type="text/javascript" src="js/tablesearch.js"></script>

<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="arranger/edit/new"><?php echo $create_link; ?></a></p>
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
				<th><?php echo $th_cnr; ?></th>
				<th><?php echo $th_user; ?></th>
				<th><?php echo $th_eventcount; ?></th>
				<th><?php echo $th_spots_free; ?></th>
				<th><?php echo $th_spots_booked; ?></th>
				<th><?php echo $th_lastlogin; ?></th>
				<th><?php echo $th_edit; ?></th>
				<th><?php echo $th_delete; ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user): ?>
			<tr>
				<td><?php echo $user->get('customer_nr'); ?></td>
				<td><span<?php if ($user->get('locked') == 1) { echo ' class="crossedout"'; } ?>><a href="arranger/info/<?php echo $user->get('id')?>"><?php echo $user->get('company').', '.$user->get('name'); ?></a></span></td>
				<td class="center"><a href="fair/overview/<?php echo $user->get('id'); ?>"><?php echo $user->get('event_count'); ?></a></td>
				<td class="center"><?php echo $spots[$user->get('id')]['open']; ?></td>
				<td class="center"><?php echo $spots[$user->get('id')]['booked']; ?></td>
				<td class="center"><?php if ($user->get('last_login')) { echo date('d-m-Y H:i:s', $user->get('last_login')); } ?></td>
				<td class="center"><a href="arranger/edit/<?php echo $user->get('id'); ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo $th_edit; ?>" /></a></td>
				<td class="center"><a onclick="confirmBox(event, 'Vill du verkligen ta bort arrangör <?php echo $user->get('name') ?>', 'arranger/delete/<?php echo $user->get('id'); ?>/confirmed')" href="arranger/delete/<?php echo $user->get('id'); ?>"><img src="images/icons/delete.png" alt="" title="<?php echo $th_delete; ?>" /></a></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
