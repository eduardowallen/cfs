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

<<<<<<< HEAD
<!-- Bookings start -->
<h2 class="clear"><img src="images/icons/marker_booked.png"/> <?php echo $headline; ?></h2>
=======
<h1><?php echo $headline; ?></h1>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217

<?php if (count($positions) > 0): ?>

	
	<table class="std_table use-scrolltable" style="float:left; padding-right: 16px;">
		<thead>
			<tr>
				<th><?php echo $tr_fair; ?></th>
<<<<<<< HEAD
				<th><?php echo $tr_map; ?></th>
				<th><?php echo $tr_pos; ?></th>
				<th><?php echo $tr_area; ?></th>
=======
				<th><?php echo $tr_pos; ?></th>
				<th><?php echo $tr_area; ?></th>
				<th><?php echo $tr_booker; ?></th>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
				<th><?php echo $tr_field; ?></th>
				<th><?php echo $tr_time; ?></th>
				<th data-sorter="false"><?php echo $tr_message; ?></th>
				<th data-sorter="false"><?php echo $tr_view; ?></th>
			</tr>
		</thead>
		<tbody>
<?php foreach($positions as $pos):

<<<<<<< HEAD
	$map = new FairMap;
	$map->load($pos->get('map'), 'id');

	$fair = new Fair;
	$fair->load($pos->get('exhibitor')->get('fair'), 'id');
	$maps = $fair->get('maps');
	$maps = $maps[0]->get('positions');
?>
			<tr>
				<td><a href="<?php echo BASE_URL.'mapTool/map/'.$pos->get('exhibitor')->get('fair'); ?>"> <?php echo $fair->get('name'); ?></a></td>
				<td><?php echo $map->get('name'); ?></td>
				<td><?php echo $pos->get('name'); ?></td>
				<td class="center"><?php echo $pos->get('area'); ?></td>
				<td class="center"><?php echo $pos->get('exhibitor')->get('commodity'); ?></td>
				<td><?php echo date('d-m-Y H:i:s', $pos->get('exhibitor')->get('booking_time')); ?></td>
				<td class="center" title="<?php echo htmlspecialchars($pos->get('exhibitor')->get('arranger_message')); ?>">
<?php if (strlen($pos->get('exhibitor')->get('arranger_message')) > 0): ?>
					<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos->get('exhibitor_id'); ?>" class="open-arranger-message">
=======
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
				<td><?php echo date('d-m-Y H:i:s', $pos->get('exhibitor')->get('booking_time')); ?> <?php echo TIMEZONE; ?></td>
				<td class="center" title="<?php echo htmlspecialchars($pos->get('exhibitor')->get('arranger_message')); ?>">
<?php if (strlen($pos->get('exhibitor')->get('arranger_message')) > 0): ?>
					<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos->get('exhibitor')->get('exhibitor_id'); ?>" class="open-arranger-message">
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
						<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
					</a>
<?php endif; ?>
				</td>
				<td class="center">
					<a href="<?php echo BASE_URL.'mapTool/map/'.$pos->get('exhibitor')->get('fair').'/'.$pos->get('id').'/'.$maps[0]->map; ?>" title="<?php echo $tr_view; ?>">
						<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
					</a>
				</td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
<p><?php echo $booked_notfound; ?></p>
<?php endif; ?>

<!-- Bookings end -->


<!-- Reservations start -->
<h2 class="clear"><img src="images/icons/marker_reserved.png"/> <?php echo $rheadline; ?></h2>

<?php if (count($rpositions) > 0): ?>
<<<<<<< HEAD
=======

	

<table class="std_table use-scrolltable" style="float:left; padding-right: 16px;">
	<thead>
		<tr>
			<th><?php echo $tr_fair; ?></th>
			<th><?php echo $tr_pos; ?></th>
			<th><?php echo $tr_area; ?></th>
			<th><?php echo $tr_booker; ?></th>
			<th><?php echo $tr_field; ?></th>
			<th><?php echo $tr_time; ?></th>
			<th><?php echo $tr_reserved_until; ?></th>
			<th data-sorter="false"><?php echo $tr_message; ?></th>
			<th data-sorter="false"><?php echo $tr_view; ?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach($rpositions as $pos): ?>
<?php
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217

	

<table class="std_table use-scrolltable" style="float:left; padding-right: 16px;">
	<thead>
		<tr>
			<th><?php echo $tr_fair; ?></th>
			<th><?php echo $tr_map ?></th>
			<th><?php echo $tr_pos; ?></th>
			<th><?php echo $tr_area; ?></th>
			<th><?php echo $tr_field; ?></th>
			<th><?php echo $tr_time; ?></th>
			<th><?php echo $tr_reserved_until; ?></th>
			<th data-sorter="false"><?php echo $tr_message; ?></th>
			<th data-sorter="false"><?php echo $tr_view; ?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach($rpositions as $pos):
	$map = new FairMap;
	$map->load($pos->get('map'), 'id');

	$fair = new Fair;
	$fair->load($pos->get('exhibitor')->get('fair'), 'id');
	$maps = $fair->get('maps');
	$maps = $maps[0]->get('positions');
?>
		<tr>
<<<<<<< HEAD
			<td><a href="<?php echo BASE_URL.'mapTool/map/'.$pos->get('exhibitor')->get('fair'); ?>"> <?php echo $fair->get('name'); ?></a></td>
			<td><?php echo $map->get('name'); ?></td>
			<td><?php echo $pos->get('name'); ?></td>
			<td class="center"><?php echo $pos->get('area'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('commodity'); ?></td>
			<td><?php echo date('d-m-Y H:i:s', $pos->get('exhibitor')->get('booking_time')); ?></td>
			<td><?php echo date('d-m-Y H:i:s', strtotime($pos->get('expires'))); ?></td>
=======
			<td><?php echo $fair->get('name'); ?></td>
			<td><?php echo $pos->get('name'); ?></td>
			<td class="center"><?php echo $pos->get('area'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('company'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('commodity'); ?></td>
			<td><?php echo date('d-m-Y H:i:s', $pos->get('exhibitor')->get('booking_time')); ?> <?php echo TIMEZONE; ?></td>
			<td><?php echo date('d-m-Y H:i:s', strtotime($pos->get('expires'))); ?> <?php echo TIMEZONE; ?></td>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			<td class="center" title="<?php echo htmlspecialchars($pos->get('exhibitor')->get('arranger_message')); ?>">
<?php if (strlen($pos->get('exhibitor')->get('arranger_message')) > 0): ?>
				<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos->get('exhibitor')->get('exhibitor_id'); ?>" class="open-arranger-message">
					<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
				</a>
<?php endif; ?>
			</td>
			<td class="center">
				<a href="<?php echo BASE_URL.'mapTool/map/'.$pos->get('exhibitor')->get('fair').'/'.$pos->get('id').'/'.$maps[0]->map; ?>" title="<?php echo $tr_view; ?>">
					<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
				</a>
			</td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>

<?php else: ?>
<p><?php echo $reserv_notfound; ?></p>
<?php endif; ?>

<!-- Reservations end -->


<!-- Preliminary bookings start -->
<h2 class="clear"><img src="images/icons/marker_applied.png"/> <?php echo $prel_table; ?></h2>

<?php if (count($prelpos) > 0): ?>
<table class="std_table use-scrolltable" style="float:left; padding-right: 16px;">
	<thead>
		<tr>
			<th><?php echo $tr_fair; ?></th>
<<<<<<< HEAD
			<th><?php echo $tr_map ?></th>
			<th><?php echo $tr_pos; ?></th>
			<th><?php echo $tr_area; ?></th>
=======
			<th><?php echo $tr_pos; ?></th>
			<th><?php echo $tr_area; ?></th>
			<th><?php echo $tr_booker; ?></th>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			<th><?php echo $tr_field; ?></th>
			<th><?php echo $tr_time; ?></th>
			<th data-sorter="false"><?php echo $tr_message; ?></th>
			<th data-sorter="false"><?php echo $tr_view; ?></th>
			<th data-sorter="false"><?php echo $tr_delete; ?></th>
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
<<<<<<< HEAD
			<td><a href="<?php echo BASE_URL.'mapTool/map/'.$booking_time[$count]['fair']; ?>"> <?php echo $fair->get('name'); ?></a></td>
			<td><?php echo $map->get('name'); ?></td>
			<td><?php echo $pos->get('name'); ?></td>
			<td class="center"><?php echo $pos->get('area'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('commodity'); ?></td>
			<td class="center"><?php echo date('d-m-Y H:i:s', $booking_time[$count]['booking_time']); ?></td>
			<td class="center" title="<?php echo htmlspecialchars($pos->get('exhibitor')->get('arranger_message')); ?>">
<?php if (strlen($pos->get('exhibitor')->get('arranger_message')) > 0): ?>
				<a href="administrator/arrangerMessage/preliminary/<?php echo $booking_time[$count]['id'] ?>" class="open-arranger-message">
=======
			<td><?php echo $fair->get('name'); ?></td>
			<td><?php echo $pos->get('name'); ?></td>
			<td class="center"><?php echo $pos->get('area'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('company'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('commodity'); ?></td>
			<td class="center"><?php echo date('d-m-Y H:i:s', $booking_time[$count]['booking_time']); ?> <?php echo TIMEZONE; ?></td>
			<td class="center" title="<?php echo htmlspecialchars($pos->get('exhibitor')->get('arranger_message')); ?>">
<?php if (strlen($pos->get('exhibitor')->get('arranger_message')) > 0): ?>
				<a href="administrator/arrangerMessage/preliminary/<?php echo $pos->get('preliminary_id'); ?>" class="open-arranger-message">
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
					<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
				</a>
<?php endif; ?>
			</td>
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
<?php else: ?>
<p><?php echo $prel_notfound; ?></p>
<?php endif; ?>

<<<<<<< HEAD
<!-- Preliminary bookings end -->

<!-- Fair registrations start-->
<h2 class="clear"><img src="images/icons/script.png"/> <?php echo $fair_registrations_headline; ?></h2>
=======

<h2 class="clear"><?php echo $fair_registrations_headline; ?></h2>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217

<?php if (count($fair_registrations) > 0): ?>
<table class="std_table use-scrolltable" style="float:left; padding-right: 16px;">
	<thead>
		<tr>
			<th><?php echo $tr_fair; ?></th>
			<th><?php echo $tr_area; ?></th>
			<th><?php echo $tr_field; ?></th>
			<th><?php echo $tr_time; ?></th>
			<th data-sorter="false"><?php echo $tr_message; ?></th>
			<th data-sorter="false"><?php echo $tr_delete; ?></th>
		</tr>
	</thead>
	<tbody>
<?php	foreach ($fair_registrations as $registration): ?>
		<tr>
<<<<<<< HEAD
			<td><a href="<?php echo BASE_URL.'mapTool/map/' . $registration->fair; ?>"> <?php echo uh($registration->fair_name); ?></a></td>
			<td class="center"><?php echo uh($registration->area); ?></td>
			<td class="center"><?php echo uh($registration->commodity); ?></td>
			<td class="center"><?php echo date('d-m-Y H:i:s', $registration->booking_time); ?></td>
			<td class="center" title="<?php echo htmlspecialchars($registration->arranger_message); ?>">
=======
			<td><?php echo uh($registration->fair_name); ?></td>
			<td class="center"><?php echo uh($registration->area); ?></td>
			<td class="center"><?php echo uh($registration->commodity); ?></td>
			<td class="center"><?php echo date('d-m-Y H:i:s', $registration->booking_time); ?> <?php echo TIMEZONE; ?></td>
			<td class="center" title="<?php echo htmlspecialchars($pos->get('exhibitor')->get('arranger_message')); ?>">
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
<?php		if (strlen($registration->arranger_message) > 0): ?>
				<a href="administrator/arrangerMessage/registration/<?php echo $registration->id; ?>" class="open-arranger-message">
					<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
				</a>
<?php		endif; ?>
			</td>
			<td class="center">
				<a href="<?php echo BASE_URL . 'exhibitor/registration_delete/' . $registration->id; ?>" title="<?php echo $tr_delete; ?>" onclick="return confirm('<?php echo $confirm_delete; ?>');">
					<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
				</a>
			</td>
		</tr>
<?php	endforeach; ?>
	</tbody>
</table>
<?php else: ?>
<p><?php echo $fregistrations_notfound; ?></p>
<?php endif; ?>
<<<<<<< HEAD

<!-- Fair registrations end -->


<!-- Old bookings start -->
<h2 class="clear"> <?php echo $old_bookings_headline; ?></h2>

<?php if (count($oldpositions) > 0): ?>

	

<table class="std_table use-scrolltable" style="float:left; padding-right: 16px;">
	<thead>
		<tr>
			<th><?php echo $tr_fair; ?></th>
			<th><?php echo $tr_map; ?></th>
			<th><?php echo $tr_pos; ?></th>
			<th><?php echo $tr_area; ?></th>
			<th><?php echo $tr_field; ?></th>
			<th><?php echo $tr_time; ?></th>
			<th data-sorter="false"><?php echo $tr_message; ?></th>
			<th data-sorter="false"><?php echo $tr_view; ?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach($oldpositions as $pos):

$map = new FairMap;
$map->load($pos->get('map'), 'id');

$fair = new Fair;
$fair->load($pos->get('exhibitor')->get('fair'), 'id');

$maps = $fair->get('maps');
$maps = $maps[0]->get('positions');
?>
		<tr>
			<td><a href="<?php echo BASE_URL.'mapTool/map/'.$pos->get('exhibitor')->get('fair'); ?>"> <?php echo $fair->get('name'); ?></a></td>
			<td><?php echo $map->get('name'); ?></td>
			<td><?php echo $pos->get('name'); ?></td>
			<td class="center"><?php echo $pos->get('area'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('commodity'); ?></td>
			<td><?php echo date('d-m-Y H:i:s', $pos->get('exhibitor')->get('booking_time')); ?></td>
			<td class="center" title="<?php echo htmlspecialchars($pos->get('exhibitor')->get('arranger_message')); ?>">
<?php if (strlen($pos->get('exhibitor')->get('arranger_message')) > 0): ?>
				<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos->get('exhibitor')->get('exhibitor_id'); ?>" class="open-arranger-message">
					<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
				</a>
<?php endif; ?>
			</td>
			<td class="center">
				<a href="<?php echo BASE_URL.'mapTool/map/'.$pos->get('exhibitor')->get('fair').'/'.$pos->get('id').'/'.$maps[0]->map; ?>" title="<?php echo $tr_view; ?>">
					<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
				</a>
			</td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>

<?php else: ?>
<p><?php echo $oldbookings_notfound; ?></p>
<?php endif; ?>

<!-- Old bookings end -->
=======
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
