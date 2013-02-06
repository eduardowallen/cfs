<h1><?php echo $headline; ?></h1>

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
<?php

$fair = new Fair;
$fair->load($pos->get('exhibitor')->get('fair'), 'id');
$maps = $fair->get('maps');
$maps = $maps[0]->get('positions');
?>
	<tr>
		<td><?php echo $fair->get('name'); ?></td>
		<td><?php echo $pos->get('name'); ?></td>
		<td class="center"><?php echo $pos->get('area'); ?></td>
		<td class="center"><?php echo $pos->get('exhibitor')->get('company'); ?></td>
		<td class="center"><?php echo $pos->get('exhibitor')->get('commodity'); ?></td>
		<td><?php echo date('d-m-Y H:i:s', $pos->get('exhibitor')->get('booking_time')); ?></td>
		<td><?php echo substr($pos->get('exhibitor')->get('arranger_message'), 0, 50); ?></td>
		<td class="center">
			<a href="<?php echo BASE_URL.'mapTool/map/'.$pos->get('exhibitor')->get('fair').'/'.$pos->get('exhibitor')->get('position').'/'.$maps[0]->map; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'exhibitor/delete/'.$pos->get('exhibitor')->get('exhibitor_id').'/'.$_SESSION['user_id'].'/'.$pos->get('exhibitor')->get('position'); ?>" title="<?php echo $tr_delete; ?>" onclick="return confirm('<?php echo $confirm_delete; ?>');">
				<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
			</a>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

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
		<th><?php echo $tr_delete; ?></th>
	</tr>
</thead>
<tbody>
<?php foreach($rpositions as $pos): ?>
<?php

$fair = new Fair;
$fair->load($pos->get('exhibitor')->get('fair'), 'id');
$maps = $fair->get('maps');
$maps = $maps[0]->get('positions');
?>
	<tr>
		<td><?php echo $fair->get('name'); ?></td>
		<td><?php echo $pos->get('name'); ?></td>
		<td class="center"><?php echo $pos->get('area'); ?></td>
		<td class="center"><?php echo $pos->get('exhibitor')->get('company'); ?></td>
		<td class="center"><?php echo $pos->get('exhibitor')->get('commodity'); ?></td>
		<td><?php echo date('d-m-Y H:i:s', $pos->get('exhibitor')->get('booking_time')); ?></td>
		<td><?php echo substr($pos->get('exhibitor')->get('arranger_message'), 0, 50); ?></td>
		<td class="center">
			<a href="<?php echo BASE_URL.'mapTool/map/'.$pos->get('exhibitor')->get('fair').'/'.$pos->get('exhibitor')->get('position').'/'.$maps[0]->map; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'exhibitor/delete/'.$pos->get('exhibitor')->get('exhibitor_id').'/'.$_SESSION['user_id'].'/'.$pos->get('exhibitor')->get('position'); ?>" title="<?php echo $tr_delete; ?>" onclick="return confirm('<?php echo $confirm_delete; ?>');">
				<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
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
		<th><?php echo $tr_delete; ?></th>
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
			<a href="<?php echo BASE_URL.'exhibitor/pre_delete/'.$booking_time[0]['id'].'/'.$_SESSION['user_id'].'/'.$booking_time[0]['position']; ?>" title="<?php echo $tr_delete; ?>" onclick="return confirm('<?php echo $confirm_delete; ?>');">
				<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
			</a>
		</td>

	</tr>
<?php endforeach; ?>
</tbody>
</table>
