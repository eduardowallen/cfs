<script>
	$(document).ready(function(){
		setTimeout(function(){
			$('.table_set').each(function(idx, table_set) {
				table_set = $(table_set);

				$('.tblrow1', table_set).each(function(idx_cell, faux_cell) {
					$(faux_cell).css('width', $('th', table_set).eq(idx_cell).width() + 'px');
				});

				$('table', table_set).css('margin-top', '-' + $('thead', table_set).height() + 'px');
				$('thead', table_set).css('visibility', 'hidden');
			});
		}, 500);
	});
</script>

<h1><?php echo $headline; ?></h1>

<div class="table_set">
	<div class="tblHeader">
		<ul class="special">
			<li><div class="tblrow1"><?php echo $tr_fair; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_pos; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_area; ?> (m<sup>2</sup>)</div></li>
			<li><div class="tblrow1"><?php echo $tr_booker; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_field; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_time; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_message; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_view; ?></div></li>
		</ul>
	</div>
	<div class="scrolltbl onlyfive">
		<table class="std_table" style="float:left; padding-right: 16px;">
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
				</tr>
			</thead>
			<tbody>
<?php foreach($positions as $pos):

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
						<a href="<?php echo BASE_URL.'mapTool/map/'.$pos->get('exhibitor')->get('fair').'/'.$pos->get('id').'/'.$maps[0]->map; ?>" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<h2 class="clear"><?php echo $rheadline; ?></h2>

<div class="table_set">
	<div class="tblHeader">
		<ul class="special">
			<li><div class="tblrow1"><?php echo $tr_fair; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_pos; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_area; ?> (m<sup>2</sup>)</div></li>
			<li><div class="tblrow1"><?php echo $tr_booker; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_field; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_time; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_reserved_until; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_message; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_view; ?></div></li>
		</ul>
	</div>
	<div class="scrolltbl onlyfive">
		<table class="std_table" style="float:left; padding-right: 16px;">
			<thead>
				<tr>
					<th><?php echo $tr_fair; ?></th>
					<th><?php echo $tr_pos; ?></th>
					<th><?php echo $tr_area; ?> (m<sup>2</sup>)</th>
					<th><?php echo $tr_booker; ?></th>
					<th><?php echo $tr_field; ?></th>
					<th><?php echo $tr_time; ?></th>
					<th><?php echo $tr_reserved_until; ?></th>
					<th><?php echo $tr_message; ?></th>
					<th><?php echo $tr_view; ?></th>
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
					<td><?php echo date('d-m-Y', strtotime($pos->get('expires'))); ?></td>
					<td><?php echo substr($pos->get('exhibitor')->get('arranger_message'), 0, 50); ?></td>
					<td class="center">
						<a href="<?php echo BASE_URL.'mapTool/map/'.$pos->get('exhibitor')->get('fair').'/'.$pos->get('id').'/'.$maps[0]->map; ?>" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<h2 class="clear"><?php echo $prel_table; ?></h2>

<div class="table_set">
	<div class="tblHeader">
		<ul class="special">
			<li><div class="tblrow1"><?php echo $tr_fair; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_pos; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_area; ?> (m<sup>2</sup>)</div></li>
			<li><div class="tblrow1"><?php echo $tr_booker; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_field; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_time; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_message; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_view; ?></div></li>
			<li><div class="tblrow1"><?php echo $tr_delete; ?></div></li>
		</ul>
	</div>
	<div class="scrolltbl onlyfive">
		<table class="std_table" style="float:left; padding-right: 16px;">
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
<?php
$count = 0;
foreach($prelpos as $pos):

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
					<td class="center"><?php echo date('d-m-Y H:i:s', $booking_time[$count]['booking_time']); ?></td>
					<td><?php echo substr($pos->get('exhibitor')->get('arranger_message'), 0, 50); ?></td>
					<td class="center">
						<a href="<?php echo BASE_URL.'mapTool/map/'.$booking_time[$count]['fair'].'/'.$pos->get('id').'/'.$maps[0]->map; ?>" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td class="center">
						<a href="<?php echo BASE_URL.'exhibitor/pre_delete/'.$booking_time[$count]['id'].'/'.$_SESSION['user_id'].'/'.$booking_time[$count]['position']; ?>" title="<?php echo $tr_delete; ?>" onclick="return confirm('<?php echo $confirm_delete; ?>');">
							<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
						</a>
					</td>

				</tr>
<?php $count++?>
<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
