<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<?php if ($hasRights): ?>

<!--
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
</table> -->

<table class="std_table" style="width: 100%;">
<thead>
	<tr>
		<th><?php echo $tr_fair; ?></th>
		<th><?php echo $tr_pos; ?></th>
		<th><?php echo $tr_area; ?> (m<sup>2</sup>)</th>
		<th><?php echo $tr_booker; ?></th>
		<th><?php echo $tr_field; ?></th>
		<th><?php echo $tr_time; ?></th>
		<th><?php echo $tr_message; ?></th>
		<th><?php echo $tr_view; ?></th>
		<th><?php echo $tr_delete; ?></th>
	</tr>
</thead>
<tbody>
<?php foreach($positions as $pos): ?>
	<tr>
		<td><?php echo $fair->get('name'); ?></td>
		<td><?php echo $pos['name']; ?></td>
		<td class="center"><?php echo $pos['area']; ?></td>
		<td class="center"><?php echo $pos['company']; ?></td>
		<td class="center"><?php echo $pos['commodity']; ?></td>
		<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
		<td><?php echo substr($pos['arranger_message'], 0, 50); ?></td>
		<td class="center">
			<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position']; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
<!--
<h2><?php echo $rheadline; ?></h2>

<table class="std_table" style="width: 100%;">
<thead>
	<tr>
		<th><?php echo $tr_fair; ?></th>
		<th><?php echo $tr_pos; ?></th>
		<th><?php echo $tr_area; ?> (m<sup>2</sup>)</th>
		<th><?php echo $tr_booker; ?></th>
		<th><?php echo $tr_field; ?></th>
		<th><?php echo $tr_time; ?></th>
		<th><?php echo $tr_message; ?></th>
		<th><?php echo $tr_view; ?></th>
		<th><?php echo $tr_deny; ?></th>
		<th><?php echo $tr_approve; ?></th>
	</tr>
</thead>
<tbody>
<?php foreach($rpositions as $pos): ?>
	<tr>
		<td><?php echo $fair->get('name'); ?></td>
		<td><?php echo $pos['name']; ?></td>
		<td class="center"><?php echo $pos['area']; ?></td>
		<td class="center"><?php echo $pos['company']; ?></td>
		<td class="center"><?php echo $pos['commodity']; ?></td>
		<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
		<td><?php echo substr($pos['arranger_message'], 0, 50); ?></td>
		<td class="center">
			<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position']; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/newReservations/deny/'.$pos['id'] ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/newReservations/approve/'.$pos['id'] ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

<h2 style="margin-top:20px"><?php echo $prel_table; ?></h2>
<table class="std_table" style="width: 100%;">
<thead>
	<tr>
		<th><?php echo $tr_fair; ?></th>
		<th><?php echo $tr_pos; ?></th>
		<th><?php echo $tr_area; ?> (m<sup>2</sup>)</th>
		<th><?php echo $tr_booker; ?></th>
		<th><?php echo $tr_field; ?></th>
		<th><?php echo $tr_time; ?></th>
		<th><?php echo $tr_message; ?></th>
		<th><?php echo $tr_view; ?></th>
		<th><?php echo $tr_deny; ?></th>
		<th><?php echo $tr_approve; ?></th>
		<th><?php echo $tr_reserve; ?></th>
	</tr>
</thead>
<tbody>
<?php foreach($prelpos as $pos): ?>
<?php
$booking_time = $pos->get('exhibitor')->getPreliminaries($_SESSION['user_id']);

$map = new FairMap;
$map->load($pos->get('map'), 'id');

$fair = new Fair;
$fair->load($map->get('fair'), 'id');
$maps = $fair->get('maps');
$maps = $maps[0]->get('positions');
//$maps = $maps[2]->get('positions');
?>
	<tr>
		<td><?php echo $fair->get('name'); ?></td>
		<td><?php echo $pos->get('name'); ?></td>
		<td class="center"><?php echo $pos->get('area'); ?></td>
		<td class="center"><?php echo $pos->get('exhibitor')->get('company'); ?></td>
		<td class="center"><?php echo $pos->get('exhibitor')->get('commodity'); ?></td>
		<td class="center"><?php echo date('d-m-Y H:i:s', $booking_time[0]['booking_time']); ?></td>
		<td><?php echo substr($pos->get('exhibitor')->get('arranger_message'), 0, 50); ?></td>
		<td class="center">
			<a href="<?php echo BASE_URL.'mapTool/map/'.$booking_time[0]['fair'].'/'.$booking_time[0]['position'].'/'.$maps[0]->map; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/newReservations/deny/'.$pos['id'] ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/newReservations/approve/'.$pos['id'] ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'exhibitor/pre_delete/'.$booking_time[0]['id'].'/'.$_SESSION['user_id'].'/'.$booking_time[0]['position']; ?>" title="<?php echo $tr_delete; ?>" onclick="return confirm('<?php echo $confirm_delete; ?>');">
				<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_delete; ?>" />
			</a>
		</td>

	</tr>
<?php endforeach; ?>
</tbody>
</table>
-->

<?php else: ?>

<p>Du är inte behörig att administrera den här mässan.</p>

<?php endif; ?>