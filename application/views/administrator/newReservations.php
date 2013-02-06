<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<?php if ($hasRights): ?>

<h2><?php echo $fair->get('name'); ?></h2>
<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_name; ?></th>
			<th><?php echo $th_area; ?></th>
			<th><?php echo $th_company; ?></th>
			<th><?php echo $th_trade; ?></th>
			<th><?php echo $th_time; ?></th>
			<th><?php echo $th_message; ?></th>
			<th><?php echo $th_profile; ?></th>
			<th><?php echo $th_goto; ?></th>
			<th><?php echo $th_approve; ?></th>
			<th><?php echo $th_reserve; ?></th>
			<th><?php echo $th_deny; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($fair->get('maps') as $map): ?>
		<?php if (in_array($map->get('id'), $accessible_maps)): ?>
			<?php foreach ($map->get('positions') as $pos): ?>
			<?php foreach ($pos->get('preliminaries') as $prel): ?>
			<tr>
				<td class="center"><?php echo $pos->get('name'); ?></td>
				<td class="center"><?php echo $pos->get('area'); ?></td>
				<td class="center"><?php echo $prel->get('user_object')->get('company'); ?></td>
				<td class="center"><?php echo $prel->get('commodity'); ?></td>
				<td class="center"><?php echo date('Y-m-d H:i', $prel->get('booking_time')); ?></td>
				<td class="center"><?php echo $prel->get('arranger_message'); ?></td>
				<td class="center"><a href="exhibitor/profile/<?php echo $prel->get('user_object')->get('id'); ?>"><img src="images/icons/user.png" alt=""/></a></td>
				<td class="center"><a href="mapTool/map/<?php echo $fair->get('id') ?>/<?php echo $pos->get('id') ?>/<?php echo $map->get('id') ?>"><img src="images/icons/map_go.png" alt=""/></a></td>
				<td class="center"><a href="administrator/newReservations/approve/<?php echo $prel->get('id'); ?>"><img src="images/icons/add.png" alt=""/></a></td>
				<td class="center"><a href="mapTool/map/<?php echo $fair->get('id') ?>/<?php echo $pos->get('id') ?>/<?php echo $map->get('id') ?>/reserve"><img src="images/icons/add.png" alt=""/></a></td>
				<td class="center"><a href="administrator/newReservations/deny/<?php echo $prel->get('id'); ?>"><img src="images/icons/delete.png" alt=""/></a></td>
			</tr>
			<?php endforeach; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php endforeach; ?>
	</tbody>
</table>

<?php else: ?>

<p>Du är inte behörig att administrera den här mässan.</p>

<?php endif; ?>